#!/usr/bin/env python3
import argparse
import json
import os
from pathlib import Path
import re
import sys
import tempfile


def write_env(path, values):
    fd, temporary = tempfile.mkstemp(prefix=f".{path.name}.", dir=path.parent)
    try:
        with os.fdopen(fd, "w", encoding="utf-8", newline="\n") as handle:
            for key, value in sorted(values.items()):
                quoted = json.dumps(value.replace("$", "$$"), ensure_ascii=False)
                handle.write(f"{key}={quoted}\n")
        os.replace(temporary, path)
    finally:
        Path(temporary).unlink(missing_ok=True)


parser = argparse.ArgumentParser()
parser.add_argument("app_dir", type=Path)
parser.add_argument("output_dir", type=Path)
parser.add_argument("rank", type=int, choices=range(4))
parser.add_argument("node_ip")
args = parser.parse_args()

sys.dont_write_bytecode = True
sys.path.insert(0, str(args.app_dir.resolve()))
from cluster_topology import Topology, split_nodes, validate_launch

keys = re.findall(
    r"^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=",
    (args.app_dir / ".env").read_text(encoding="utf-8"),
    re.MULTILINE,
)
values = {key: os.environ.get(key, "") for key in keys}
local_ip = os.environ.get("LOCAL_IP", "")
if not local_ip:
    parser.error("LOCAL_IP must be exported from the application .env")
home = Path(os.environ["HOME"])
values.update(
    NODE_RANK=str(args.rank),
    HEADLESS="" if args.rank == 0 else "1",
    MASTER_ADDR=local_ip,
    MASTER_PORT=os.environ.get("MASTER_PORT") or "29501",
    VLLM_HOST_IP=args.node_ip,
    HF_CACHE=os.environ.get("HF_HOME") or str(home / ".cache/huggingface"),
    VLLM_CACHE=str(home / ".cache/vllm"),
    FLASHINFER_CACHE=str(home / ".cache/flashinfer"),
    B12X_CACHE=str(home / ".cache/b12x"),
    TRITON_CACHE=str(home / ".triton"),
    TILELANG_CACHE=str(home / ".tilelang"),
)
container_values = {
    "HEADLESS": values["HEADLESS"],
    "NCCL_IGNORE_CPU_AFFINITY": "1",
    "VLLM_HOST_IP": args.node_ip,
    "RAY_NODE_IP_ADDRESS": args.node_ip,
    "RAY_OVERRIDE_NODE_IP_ADDRESS": args.node_ip,
    "MN_IF_NAME": values.get("ETH_IF", ""),
    "UCX_NET_DEVICES": values.get("ETH_IF", ""),
    "NCCL_SOCKET_IFNAME": values.get("ETH_IF", ""),
    "NCCL_IB_HCA": values.get("IB_IF", ""),
    "NCCL_IB_DISABLE": "0",
    "OMPI_MCA_btl_tcp_if_include": values.get("ETH_IF", ""),
    "GLOO_SOCKET_IFNAME": values.get("ETH_IF", ""),
    "TP_SOCKET_IFNAME": values.get("ETH_IF", ""),
    "RAY_memory_monitor_refresh_ms": "0",
    "RAY_num_prestart_python_workers": "0",
    "RAY_object_store_memory": "1073741824",
}
container_values.update(
    {
        key.removeprefix("CONTAINER_"): values[key]
        for key in keys
        if key.startswith("CONTAINER_") and key != "CONTAINER_NAME"
    }
)
container_values.update(
    {
        key: values.get(key, "")
        for key in ("DSPARK_MODEL_CONTAINER", "SERVED_MODEL_NAME", "VLLM_PORT", "VLLM_API_KEY")
    }
)
try:
    topology = Topology.from_env(values)
    if topology:
        command = (args.app_dir / "appliance/deepseek-v4.1-flash.sh").read_text(encoding="utf-8")
        container_values.update(
            validate_launch(
                topology,
                split_nodes(values.get("CLUSTER_NODES", "")),
                command,
                env=container_values,
            )
        )
except (ValueError, OSError) as error:
    parser.error(str(error))
args.output_dir.mkdir(parents=True, exist_ok=True)
write_env(args.output_dir / ".env", values)
write_env(args.output_dir / "container.env", container_values)
