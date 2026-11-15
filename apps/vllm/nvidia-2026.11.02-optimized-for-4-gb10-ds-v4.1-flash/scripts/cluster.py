#!/usr/bin/env python3
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


RING_ENV = {
    "NCCL_ALGO": "Ring",
    "NCCL_NET_PLUGIN": "none",
    "NCCL_IB_SUBNET_AWARE_ROUTING": "1",
    "NCCL_IB_MERGE_NICS": "0",
}


class Topology:
    def __init__(self, nodes, links):
        self.nodes = list(nodes)
        if not self.nodes or len(set(self.nodes)) != len(self.nodes):
            raise ValueError("Topology requires a nonempty, unique CLUSTER_NODES list")
        for node in self.nodes:
            ipaddress.IPv4Address(node)
        if not isinstance(links, list):
            raise ValueError("CLUSTER_LINKS must be a JSON list of two-endpoint objects")
        self.links = links
        self.adj = {node: set() for node in self.nodes}
        owners = {node: node for node in self.nodes}
        seen = set()
        for link in links:
            if not isinstance(link, dict) or len(link) != 2:
                raise ValueError("Each CLUSTER_LINKS entry must map two nodes to their link IPs")
            a, b = link
            if a not in self.adj or b not in self.adj:
                raise ValueError("CLUSTER_LINKS contains a node absent from CLUSTER_NODES")
            for node, address in link.items():
                ipaddress.IPv4Address(address)
                if address in owners and owners[address] != node:
                    raise ValueError("A link IP belongs to more than one node")
                owners[address] = node
            identity = tuple(sorted(link.items()))
            if identity in seen:
                raise ValueError("CLUSTER_LINKS contains a duplicate link")
            seen.add(identity)
            self.adj[a].add(b)
            self.adj[b].add(a)
        connected = {self.nodes[0]}
        pending = [self.nodes[0]]
        while pending:
            for peer in self.adj[pending.pop()]:
                if peer not in connected:
                    connected.add(peer)
                    pending.append(peer)
        if len(connected) != len(self.nodes):
            raise ValueError("CLUSTER_LINKS is disconnected")

    @classmethod
    def from_env(cls, env):
        if not env.get("CLUSTER_LINKS"):
            return None
        try:
            links = json.loads(env["CLUSTER_LINKS"])
        except json.JSONDecodeError as exc:
            raise ValueError("CLUSTER_LINKS is not valid JSON") from exc
        topology = cls([node.strip() for node in env.get("CLUSTER_NODES", "").split(",") if node.strip()], links)
        if env.get("LOCAL_IP") and env["LOCAL_IP"] != topology.nodes[0]:
            raise ValueError("With CLUSTER_LINKS, LOCAL_IP must be first in CLUSTER_NODES")
        return topology

    @property
    def ring(self):
        return len(self.nodes) >= 4 and all(len(peers) == 2 for peers in self.adj.values())

    @property
    def complete(self):
        return all(len(peers) == len(self.nodes) - 1 for peers in self.adj.values())

    def subset(self, nodes):
        if not set(nodes) <= set(self.nodes):
            raise ValueError("Selected nodes are absent from the saved topology")
        return Topology(nodes, [link for link in self.links if set(link) <= set(nodes)])

    def validate_order(self, nodes):
        selected = self.subset(nodes)
        if selected.complete:
            return
        if not selected.ring:
            raise ValueError("Selected nodes break the ring; use the full ring or a directly connected pair")
        if any(b not in selected.adj[a] for a, b in zip(nodes, nodes[1:] + nodes[:1])):
            raise ValueError("CLUSTER_NODES must follow physical ring order; check the configured node order")


def validate_launch(topology, nodes, command="", env=None):
    """Check the actual selected ranks before any deployment or container cleanup."""
    if not nodes or nodes[0] != topology.nodes[0]:
        raise ValueError("The saved local/head node must remain first in the selected ranks")
    effective_env = dict(env or {})
    for line in command.splitlines():
        if line.strip().startswith("export "):
            for assignment in shlex.split(line.strip()[7:], comments=True):
                if "=" in assignment:
                    key, value = assignment.split("=", 1)
                    effective_env[key] = value
    sizes = []
    for name, short in (("tensor", "tp"), ("pipeline", "pp"), ("data", "dp")):
        matches = re.findall(r"(?:^|\s)--?" + rf"(?:{name}-parallel-size|{short})(?:=|\s+)(\d+)", command)
        sizes.append(int(matches[-1]) if matches else 1)
    tp, pp, dp = sizes
    if any(size < 1 for size in sizes):
        raise ValueError("Parallelism sizes must be positive")
    has_sizes = bool(re.search(r"--?(?:tensor-parallel-size|pipeline-parallel-size|data-parallel-size|tp|pp|dp)(?:=|\s)", command))
    if has_sizes:
        required = tp * pp * dp
        if required > len(nodes):
            raise ValueError("Requested parallelism exceeds the number of configured nodes")
        nodes = nodes[:required]
    topology.validate_order(nodes)
    if topology.ring and len(nodes) > 2:
        if has_sizes and (pp != 1 or dp != 1):
            raise ValueError("Ring topology currently supports tensor parallelism only (PP=1, DP=1)")
        for key, value in RING_ENV.items():
            if key in effective_env and str(effective_env[key]) != value:
                raise ValueError(f"Ring topology requires {key}={value}")
        return RING_ENV
    return {}


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
    if node != NODES[0]:
        args = ["ssh", "-o", "BatchMode=yes", "-o", "ConnectTimeout=10",
                "-o", "ServerAliveInterval=5", "-o", "ServerAliveCountMax=3",
                node, shlex.join(args)]
    result = subprocess.run(args, input=input, stdout=subprocess.PIPE if capture else None,
                            text=True, timeout=timeout, env=env)
    if check and result.returncode:
        raise RuntimeError(f"Command failed on {node}: {shlex.join(args[-1:] if node != NODES[0] else args)}")
    return result


def inspect_container(node):
    names = run(node, ["docker", "ps", "-a", "--filter", f"name=^/{CONTAINER_NAME}$",
                       "--format", "{{.Names}}"], capture=True).stdout.splitlines()
    if CONTAINER_NAME not in names:
        return None
    container = json.loads(run(node, ["docker", "inspect", CONTAINER_NAME], capture=True).stdout)[0]
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
                compose(node, "down", "--timeout", "10")
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
    topology = Topology.from_env(os.environ)
    extra_env = {key.removeprefix("CONTAINER_"): value.replace("$", "$$")
                 for key, value in os.environ.items()
                 if key.startswith("CONTAINER_") and key != "CONTAINER_NAME"}
    for rank, node in enumerate(NODES):
        environment = dict(os.environ, NODE_RANK=str(rank), VLLM_HOST_IP=node,
                           MASTER_ADDR=NODES[0], COMPOSE_DISABLE_ENV_FILE="1")
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
        env = service.setdefault("environment", {})
        for key, value in extra_env.items():
            env.setdefault(key, value)
        if topology:
            command = service.get("command", [])
            if isinstance(command, list):
                command = " ".join(command)
            env.update(validate_launch(topology, NODES, command, env=env))
        configs[node] = config
    return configs


def start():
    wait_attempts = int(os.environ.get("WAIT_ATTEMPTS", "100"))
    wait_seconds = int(os.environ.get("WAIT_SECONDS", "15"))
    if wait_attempts < 1 or wait_seconds < 1:
        raise ValueError("WAIT_ATTEMPTS and WAIT_SECONDS must be positive integers")
    policy = os.environ.get("IMAGE_PULL_POLICY", "if_not_present")
    if policy not in ("always", "if_not_present", "never"):
        raise RuntimeError(f"Unsupported IMAGE_PULL_POLICY: {policy}")
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

    head_id = None
    head_fingerprint = None
    for node in NODES:
        print(f"Preparing image {image} on {node} (policy: {policy})...", flush=True)
        present = run(node, ["docker", "image", "inspect", "--format", "{{.Id}}", image],
                      capture=True, check=False)
        if policy == "always" or (policy == "if_not_present" and present.returncode):
            run(node, ["docker", "pull", image], timeout=3000)
        metadata = json.loads(run(node, ["docker", "image", "inspect", image], capture=True).stdout)
        if head_id is None:
            head_id = metadata[0]["Id"]
            head_fingerprint = fingerprint(metadata)
        elif metadata[0]["Id"] != head_id and fingerprint(metadata) != head_fingerprint:
            raise RuntimeError(f"Docker image on {node} does not match the head node")

    for node, config in configs.items():
        target = str(CONFIG_PATHS[node])
        directory = str(CONFIG_PATHS[node].parent)
        payload = json.dumps(config, ensure_ascii=False) + "\n"
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
        print("Waiting for Docker healthchecks on all four nodes...", flush=True)
        for _ in range(wait_attempts):
            healthy = True
            for node in NODES:
                container = inspect_container(node)
                if not container or not container["State"]["Running"] or container["State"].get("Restarting"):
                    raise RuntimeError(f"vLLM exited on {node}; inspect Docker logs for {CONTAINER_NAME}")
                healthy = healthy and container["State"].get("Health", {}).get("Status") == "healthy"
            if healthy:
                print(f"DeepSeek-V4.1-Flash is ready on port {os.environ['VLLM_PORT']}.", flush=True)
                return
            time.sleep(wait_seconds)
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
    if len(NODES) != 4 or len(set(NODES)) != 4 or NODES[0] != os.environ["LOCAL_IP"]:
        raise ValueError("Configure four distinct CLUSTER_NODES with LOCAL_IP as the first node")
    for node in NODES:
        ipaddress.IPv4Address(node)
    CONTAINER_NAME = os.environ["CONTAINER_NAME"]
    PROJECT_NAME = os.environ.get("PROJECT_NAME") or re.sub(r"[^a-z0-9_-]", "-", CONTAINER_NAME.lower())
    if not re.fullmatch(r"[a-z0-9][a-z0-9_-]*", PROJECT_NAME):
        raise ValueError("Invalid PROJECT_NAME")
    worker_dir = Path(os.environ.get("WORKER_DIR", "/opt/1pds41"))
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
            run(node, ["rm", "-f", "--", str(CONFIG_PATHS[node])])
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
