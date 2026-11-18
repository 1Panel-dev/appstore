#!/usr/bin/env python3
from concurrent.futures import ThreadPoolExecutor
import hashlib
import ipaddress
import json
import os
from pathlib import Path
import re
import shlex
import signal
import subprocess
import sys
import time

APP_DIR = Path(__file__).resolve().parent.parent


PREFLIGHT = r"""
import ipaddress
import json
from pathlib import Path
import subprocess
import sys

model, interface, hcas, gid, auto, match_ip, node = sys.argv[1:]
model = Path(model)
for path in (model / "config.json", model / "model.safetensors.index.json"):
    if not path.is_file():
        raise SystemExit(f"Missing model file: {path}")
if not (model / "model-fp8-mtp-ple.safetensors").is_file():
    raise SystemExit("Missing official NVIDIA MTP/PLE weights")
index = json.loads((model / "model.safetensors.index.json").read_text())
shards = set(index["weight_map"].values())
if not shards:
    raise SystemExit("Model index contains no weight shards")
for shard in shards:
    if not (model / shard).is_file():
        raise SystemExit(f"Missing weight shard: {shard}")
for path in (Path("/dev/infiniband"), Path("/sys/class/net") / interface):
    if not path.exists():
        raise SystemExit(f"Missing device or interface: {path}")


def read_attribute(path):
    try:
        return path.read_text().strip()
    except OSError:
        return ""


addresses = {}


def interface_ipv4(name):
    if name not in addresses:
        try:
            output = subprocess.check_output(["ip", "-j", "-4", "addr", "show", "dev", name],
                                             text=True, stderr=subprocess.DEVNULL)
        except (OSError, subprocess.CalledProcessError):
            addresses[name] = []
        else:
            addresses[name] = [entry["local"] for device in json.loads(output)
                               for entry in device.get("addr_info", [])
                               if entry.get("family") == "inet"]
    return addresses[name]


if auto == "1":
    local_addresses = interface_ipv4(interface)
    match_ip = match_ip or (local_addresses[0] if local_addresses else node)
    try:
        ipaddress.IPv4Address(match_ip)
    except ValueError:
        raise SystemExit(f"Invalid RoCE IPv4 on {node}: {match_ip}")

members = {}
preferred = set()
for hca in hcas.split(","):
    ports = Path("/sys/class/infiniband") / hca / "ports"
    if not ports.is_dir():
        raise SystemExit(f"Missing HCA on {node}: {hca}")
    for port in sorted(ports.iterdir()):
        state = read_attribute(port / "state").split(":")[-1].strip()
        if state and state != "ACTIVE":
            continue
        if read_attribute(port / "link_layer") not in ("", "Ethernet", "InfiniBand"):
            continue
        types = port / "gid_attrs/types"
        usable = set()
        try:
            entries = list(types.iterdir())
        except OSError as error:
            raise SystemExit(f"Cannot inspect RoCEv2 GIDs on {node}, {hca}:{port.name}: {error}")
        for entry in entries:
            if not entry.name.isdigit() or read_attribute(entry) != "RoCE v2":
                continue
            index = int(entry.name)
            if auto == "0":
                usable.add(index)
                continue
            try:
                address = ipaddress.IPv6Address(read_attribute(port / "gids" / entry.name)).ipv4_mapped
            except ipaddress.AddressValueError:
                continue
            if address is None:
                continue
            address = str(address)
            if address == match_ip:
                preferred.add(index)
            else:
                netdev = read_attribute(port / "gid_attrs/ndevs" / entry.name)
                if not netdev or address not in interface_ipv4(netdev):
                    continue
            usable.add(index)
        members[f"{hca}:{port.name}"] = usable

if not members:
    raise SystemExit(f"No active HCA ports on {node}: {hcas}")
detail = "; ".join(f"{member}={','.join(map(str, sorted(indexes))) or 'none'}"
                   for member, indexes in members.items())
common = set.intersection(*members.values())
if auto == "1":
    if not common:
        raise SystemExit(f"No common IPv4 RoCEv2 GID index on {node} (match {match_ip}): {detail}. "
                         "Check the network configuration or narrow IB_IF / WORKER_IB_IF.")
    gid = str(min(common & preferred or common))
elif not gid.isdecimal() or int(gid) not in common:
    raise SystemExit(f"Invalid RoCEv2 GID on {node}, index {gid}. Readable RoCEv2 indexes: {detail}. "
                     "Check GID_INDEX (head) or WORKER_GID_INDEX (worker).")
print(f"RoCEv2 GID on {node}: {gid} (HCA {hcas}, interface {interface}, "
      f"{'match ' + match_ip if auto == '1' else 'manual'})", file=sys.stderr)
print(gid)
"""


def omit_empty_fields(config):
    # Docker API versions differ in whether unset config fields are serialized.
    # Only normalize the field level: empty values inside Labels, Volumes, and
    # ExposedPorts carry meaning, and array order (e.g. Env or Cmd) matters.
    return {
        key: value for key, value in config.items()
        if value not in (None, "", False, 0, [], {})
    }


def fingerprint(result):
    if not isinstance(result, list) or len(result) != 1:
        raise ValueError("expected one inspected image")
    image = result[0]
    if not isinstance(image, dict):
        raise ValueError("invalid image metadata")
    for field in ("Os", "Architecture"):
        if not isinstance(image.get(field), str) or not image[field]:
            raise ValueError("missing image platform")
    config = image.get("Config")
    rootfs = image.get("RootFS")
    if not isinstance(config, dict) or not isinstance(rootfs, dict):
        raise ValueError("missing image config or filesystem")
    layers = rootfs.get("Layers")
    if layers is None:
        layers = []
    if rootfs.get("Type") != "layers" or not isinstance(layers, list):
        raise ValueError("invalid image filesystem")
    if any(not isinstance(layer, str) or not re.fullmatch(r"sha256:[0-9a-f]{64}", layer)
           for layer in layers):
        raise ValueError("invalid image layer digest")

    config = omit_empty_fields(config)
    if isinstance(config.get("Healthcheck"), dict):
        config["Healthcheck"] = omit_empty_fields(config["Healthcheck"])
        config = omit_empty_fields(config)
    content = {
        "Os": image["Os"],
        "Architecture": image["Architecture"],
        "Variant": image.get("Variant") or "",
        "OsVersion": image.get("OsVersion") or "",
        "RootFS": {"Type": "layers", "Layers": layers},
        "Config": config,
    }
    canonical = json.dumps(content, sort_keys=True, separators=(",", ":"))
    return "sha256:" + hashlib.sha256(canonical.encode("utf-8")).hexdigest()


def run(node, args, *, capture=False, input=None, timeout=60, check=True, env=None):
    command = shlex.join(args).replace("\n", "\\n")
    if len(command) > 200:
        command = command[:200] + "..."
    if node != NODES[0]:
        args = ["ssh", "-o", "BatchMode=yes", "-o", "ConnectTimeout=10",
                "-o", "ServerAliveInterval=5", "-o", "ServerAliveCountMax=3",
                node, shlex.join(args)]
    result = subprocess.run(args, input=input, stdout=subprocess.PIPE if capture else None,
                            text=True, timeout=timeout, env=env)
    if check and result.returncode:
        raise RuntimeError(f"Command failed on {node}: {command}")
    return result


def inspect_container(node, deadline=None):
    names = run(node, ["docker", "ps", "-a", "--filter", f"name=^/{CONTAINER_NAME}$",
                       "--format", "{{.Names}}"], capture=True,
                timeout=60 if deadline is None else max(0.001, min(60, deadline - time.monotonic()))).stdout.splitlines()
    if CONTAINER_NAME not in names:
        return None
    container = json.loads(run(node, ["docker", "inspect", CONTAINER_NAME], capture=True,
                               timeout=60 if deadline is None else max(0.001, min(60, deadline - time.monotonic()))).stdout)[0]
    labels = container.get("Config", {}).get("Labels") or {}
    if labels.get("com.docker.compose.project") != PROJECT_NAME or labels.get("com.docker.compose.service") != "vllm-dspark":
        raise RuntimeError(f"Container {CONTAINER_NAME} on {node} belongs to another deployment")
    return container


def compose(node, *args):
    return run(node, ["env", "COMPOSE_DISABLE_ENV_FILE=1", "docker", "compose",
                      "--project-name", PROJECT_NAME, "--env-file", "/dev/null",
                      "-f", str(CONFIG_PATHS[node]), *args], timeout=120)


def stop(nodes):
    failed = []
    for node in nodes:
        try:
            if inspect_container(node) is None:
                continue
            print(f"Stopping rank {NODES.index(node)} on {node}...", flush=True)
            if run(node, ["test", "-f", str(CONFIG_PATHS[node])], check=False).returncode == 0:
                compose(node, "down", "--timeout", "30")
            else:
                run(node, ["docker", "rm", "-f", CONTAINER_NAME])
            if inspect_container(node) is not None:
                raise RuntimeError(f"Container {CONTAINER_NAME} still exists on {node}")
        except (RuntimeError, subprocess.TimeoutExpired) as error:
            print(str(error), file=sys.stderr)
            failed.append(node)
    if failed:
        raise RuntimeError("Could not stop all nodes: " + ", ".join(failed))


def render_configs():
    configs = {}
    extra_env = {key.removeprefix("CONTAINER_"): value
                 for key, value in os.environ.items()
                 if key.startswith("CONTAINER_") and key != "CONTAINER_NAME"}
    for rank, node in enumerate(NODES):
        environment = dict(os.environ, NODE_RANK=str(rank), VLLM_HOST_IP=node,
                           MASTER_ADDR=NODES[0], COMPOSE_DISABLE_ENV_FILE="1")
        if rank:
            for key in ("ETH_IF", "IB_IF", "GID_INDEX"):
                environment[key] = os.environ.get("WORKER_" + key) or os.environ[key]
        output = run(NODES[0], ["docker", "compose", "--project-name", PROJECT_NAME,
                                "--env-file", str(APP_DIR / ".env"),
                                "-f", str(APP_DIR / "docker-compose.yml"),
                                "config", "--format", "json"], capture=True, env=environment).stdout
        config = json.loads(output)
        if set(config.get("services", {})) != {"vllm-dspark"}:
            raise RuntimeError("Compose must contain only the vllm-dspark service")
        service = config["services"]["vllm-dspark"]
        if service.get("container_name") != CONTAINER_NAME:
            raise RuntimeError("Compose container_name must match CONTAINER_NAME")
        if not service.get("healthcheck", {}).get("test") or service["healthcheck"].get("disable"):
            raise RuntimeError("Compose must define a healthcheck")
        for limit in service.get("ulimits", {}).values():
            if isinstance(limit, dict):
                limit.setdefault("soft", 0)
                limit.setdefault("hard", 0)
        env = service.setdefault("environment", {})
        for key, value in extra_env.items():
            env.setdefault(key, value)
        command = service.get("command")
        if not isinstance(command, list) or not command:
            raise RuntimeError("Compose command must be a nonempty argument list")
        if "--headless" in command:
            raise RuntimeError("Do not add --headless to Compose; it is assigned per rank")
        if rank:
            command.append("--headless")
        for volume in service.get("volumes", []):
            if volume.get("type") == "bind" and Path(volume["source"]).parent == APP_DIR / "scripts/patches":
                patch = Path(volume["source"])
                if not patch.is_file():
                    raise RuntimeError(f"Missing patch: {patch}")
                if rank:
                    volume["source"] = str(CONFIG_PATHS[node].parent / "patches" / patch.name)
        configs[node] = config
    return configs


def prepare_image(node, image, policy):
    print(f"Preparing image {image} on {node} (policy: {policy})...", flush=True)
    present = run(node, ["docker", "image", "inspect", "--format", "{{.Id}}", image],
                  capture=True, check=False)
    if policy == "always" or (policy == "if_not_present" and present.returncode):
        if node == NODES[0]:
            run(node, ["docker", "pull", image], timeout=3000)
        else:
            run(node, ["timeout", "--signal=KILL", "3000", "docker", "pull", image], timeout=3010)
    return json.loads(run(node, ["docker", "image", "inspect", image], capture=True).stdout)


def start():
    wait_attempts = int(os.environ.get("WAIT_ATTEMPTS", "200"))
    wait_seconds = int(os.environ.get("WAIT_SECONDS", "15"))
    if wait_attempts < 1 or wait_seconds < 1:
        raise ValueError("WAIT_ATTEMPTS and WAIT_SECONDS must be positive integers")
    policy = os.environ.get("IMAGE_PULL_POLICY", "if_not_present")
    if policy not in ("always", "if_not_present", "never"):
        raise RuntimeError(f"Unsupported IMAGE_PULL_POLICY: {policy}")
    auto = os.environ.get("NCCL_IB_GID_AUTO", "1")
    if auto not in ("0", "1"):
        raise ValueError("NCCL_IB_GID_AUTO must be 0 or 1")
    for node in NODES:
        prefix = "WORKER_" if node != NODES[0] else ""
        network = [os.environ.get(prefix + key) or os.environ.get(key, "")
                   for key in ("ETH_IF", "IB_IF", "GID_INDEX")]
        resolved = run(node, ["python3", "-c", PREFLIGHT, os.environ["DSPARK_MODEL_HOST"],
                              *network, auto, os.environ.get(prefix + "NCCL_IB_GID_MATCH_IP", ""), node],
                       capture=True).stdout.strip()
        if not re.fullmatch(r"[0-9]+", resolved):
            raise RuntimeError(f"Invalid GID resolver output from {node}: {resolved}")
        os.environ[prefix + "GID_INDEX"] = resolved
    configs = render_configs()
    image = configs[NODES[0]]["services"]["vllm-dspark"]["image"]
    for node in NODES:
        container = inspect_container(node)
        if container and (container["State"]["Running"] or container["State"].get("Restarting")):
            raise RuntimeError(f"Container {CONTAINER_NAME} is already running on {node}; stop it first")
        run(node, ["docker", "compose", "version"])
        run(node, ["test", "-d", os.environ["DSPARK_MODEL_HOST"]])
        if configs[node]["services"]["vllm-dspark"]["image"] != image:
            raise RuntimeError("All ranks must use the same image")

    with ThreadPoolExecutor(max_workers=len(NODES)) as executor:
        prepared_images = [executor.submit(prepare_image, node, image, policy) for node in NODES]

    head_id = None
    head_fingerprint = None
    for node, prepared in zip(NODES, prepared_images):
        metadata = prepared.result()
        if metadata[0].get("Architecture") != "arm64" or metadata[0].get("Os") != "linux":
            raise RuntimeError(f"Expected the Linux ARM64 image on {node}")
        if head_id is None:
            head_id = metadata[0]["Id"]
            head_fingerprint = fingerprint(metadata)
        elif metadata[0]["Id"] != head_id and fingerprint(metadata) != head_fingerprint:
            raise RuntimeError(f"Docker image on {node} does not match the head node")

    for node in NODES:
        processes = run(node, ["nvidia-smi", "--query-compute-apps=pid", "--format=csv,noheader"],
                        capture=True).stdout.strip()
        if processes:
            raise RuntimeError(f"GPU compute processes are already running on {node}: {processes}")

    listeners = run(NODES[0], ["ss", "-H", "-ltn",
                               f"( sport = :{os.environ['VLLM_PORT']} or sport = :{os.environ['MASTER_PORT']} )"],
                    capture=True).stdout.strip()
    if listeners:
        raise RuntimeError(f"API or master port is already listening on {NODES[0]}: {listeners}")

    cache_dir = str(Path(os.environ["CACHE_ROOT"]) / CONTAINER_NAME)
    for node, config in configs.items():
        run(node, ["mkdir", "-p", "--", *[str(Path(cache_dir) / name)
                   for name in ("huggingface", "vllm", "fi_autotune")]])
        if node != NODES[0]:
            patch_dir = CONFIG_PATHS[node].parent / "patches"
            run(node, ["mkdir", "-p", "--", str(patch_dir)])
            for name in ("mtp_v030_patched.py", "draft_vocab_en_code_47k.txt"):
                script = '''set -eu; umask 022; tmp=$(mktemp "$1.XXXXXX"); trap 'rm -f "$tmp"' EXIT; cat > "$tmp"; chmod 644 "$tmp"; mv -f "$tmp" "$1"'''
                run(node, ["bash", "-c", script, "--", str(patch_dir / name)],
                    input=(APP_DIR / "scripts/patches" / name).read_text())
        target = str(CONFIG_PATHS[node])
        directory = str(CONFIG_PATHS[node].parent)
        payload = json.dumps(config, ensure_ascii=False).replace("$", "$$") + "\n"
        if node == NODES[0]:
            temp = CONFIG_PATHS[node].with_suffix(".tmp")
            temp.write_text(payload)
            temp.chmod(0o600)
            temp.replace(CONFIG_PATHS[node])
        else:
            script = ('set -eu; umask 077; mkdir -p "$1"; '
                      'tmp=$(mktemp "$1/.compose.XXXXXX"); '
                      'trap \'rm -f "$tmp"\' EXIT; cat > "$tmp"; mv -f "$tmp" "$2"')
            run(node, ["bash", "-c", script, "--", directory, target], input=payload)

    started = []
    try:
        for node in NODES[1:] + NODES[:1]:
            print(f"Starting rank {NODES.index(node)} on {node}...", flush=True)
            started.append(node)
            compose(node, "up", "-d", "--pull", "never", "--no-build", "vllm-dspark")
        print("Waiting for Docker healthchecks on both nodes...", flush=True)
        deadline = time.monotonic() + wait_attempts * wait_seconds
        while time.monotonic() < deadline:
            healthy = True
            for node in NODES:
                container = inspect_container(node, deadline)
                if not container or not container["State"]["Running"] or container["State"].get("Restarting"):
                    raise RuntimeError(f"vLLM exited on {node}; inspect Docker logs for {CONTAINER_NAME}")
                healthy = healthy and container["State"].get("Health", {}).get("Status") == "healthy"
            if healthy and time.monotonic() < deadline:
                print(f"Qwen3.8-Flash-Next is ready on port {os.environ['VLLM_PORT']}.", flush=True)
                return
            time.sleep(max(0, min(wait_seconds, deadline - time.monotonic())))
        raise RuntimeError("Timed out waiting for Docker healthchecks")
    except Exception:
        print("Startup failed; containers are retained. Run scripts/stop.sh before retrying.", file=sys.stderr)
        for node in started:
            print(f"Recent rank {NODES.index(node)} logs on {node}:", file=sys.stderr)
            try:
                run(node, ["docker", "logs", "--tail", "120", CONTAINER_NAME], check=False)
            except (OSError, subprocess.TimeoutExpired) as error:
                print(str(error), file=sys.stderr)
        raise


def main():
    global NODES, CONTAINER_NAME, PROJECT_NAME, CONFIG_PATHS
    NODES = [node.strip() for node in os.environ["CLUSTER_NODES"].split(",")]
    if len(NODES) != 2 or len(set(NODES)) != 2 or NODES[0] != os.environ["LOCAL_IP"]:
        raise ValueError("Configure two distinct CLUSTER_NODES with LOCAL_IP as the first node")
    for node in NODES:
        ipaddress.IPv4Address(node)
    CONTAINER_NAME = os.environ["CONTAINER_NAME"]
    PROJECT_NAME = os.environ.get("PROJECT_NAME") or re.sub(r"[^a-z0-9_-]", "-", CONTAINER_NAME.lower())
    if not re.fullmatch(r"[a-z0-9][a-z0-9_-]*", PROJECT_NAME):
        raise ValueError("Invalid PROJECT_NAME")
    worker_dir = Path(os.environ.get("WORKER_DIR", "/opt/1pqw"))
    if not worker_dir.is_absolute():
        raise ValueError("WORKER_DIR must be an absolute path")
    CONFIG_PATHS = {node: worker_dir / PROJECT_NAME / "docker-compose.json" for node in NODES}
    CONFIG_PATHS[NODES[0]] = APP_DIR / ".runtime" / "docker-compose.json"
    action = sys.argv[1] if len(sys.argv) == 2 else ""
    if action not in ("start", "stop", "restart", "uninstall"):
        raise ValueError("Usage: cluster.py start|stop|restart|uninstall")
    if action in ("stop", "restart", "uninstall"):
        stop(NODES)
    if action == "uninstall":
        for node in NODES[1:]:
            print(f"Removing deployment configuration on {node}...", flush=True)
            run(node, ["rm", "-f", "--", str(CONFIG_PATHS[node]),
                       *[str(CONFIG_PATHS[node].parent / "patches" / name)
                         for name in ("mtp_v030_patched.py", "draft_vocab_en_code_47k.txt")]])
    if action in ("start", "restart"):
        start()


def interrupted(signum, frame):
    raise SystemExit(128 + signum)


if __name__ == "__main__":
    os.umask(0o077)
    for signum in (signal.SIGTERM, signal.SIGINT, signal.SIGHUP):
        signal.signal(signum, interrupted)
    try:
        main()
    except (OSError, ValueError, KeyError, RuntimeError, subprocess.TimeoutExpired) as error:
        print(f"Error: {error}", file=sys.stderr)
        sys.exit(1)
