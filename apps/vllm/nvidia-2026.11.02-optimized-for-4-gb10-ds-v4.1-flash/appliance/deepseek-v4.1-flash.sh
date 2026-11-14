#!/bin/bash
# DeepSeek-V4.1-Flash launch script for the 4x DGX Spark appliance.
#
# This runs INSIDE every node container. launch-cluster.sh appends the
# per-node --nnodes/--node-rank/--master-addr/--master-port arguments.
# Do not add --nnodes, --node-rank, --master-addr, --master-port, --headless
# or --distributed-executor-backend here.
#
# The model is bind-mounted read-only by appliance/start.sh at the path below.
# The mount and vLLM use DSPARK_MODEL_CONTAINER from .env.

set -euo pipefail

API_KEY_ARGS=()
if [ -n "${VLLM_API_KEY:-}" ]; then
    API_KEY_ARGS=("--api-key=$VLLM_API_KEY")
fi

vllm serve "${DSPARK_MODEL_CONTAINER:?}" \
    --served-model-name "${SERVED_MODEL_NAME:?}" \
    --host 0.0.0.0 \
    --port "${VLLM_PORT:?}" \
    --dtype bfloat16 \
    --tensor-parallel-size 4 \
    --load-format b12x \
    --block-size 256 \
    --enable-prefix-caching \
    --gpu-memory-utilization 0.85 \
    --default-chat-template-kwargs '{"enable_thinking":false}' \
    --max-model-len auto \
    --max-num-seqs 4 \
    --max-num-batched-tokens 4096 \
    --limit-mm-per-prompt '{"image":1}' \
    --engram-config '{"cpu_offload":false,"table_memory":"disk"}' \
    --linear-backend b12x \
    --moe-backend b12x \
    --attention-backend b12x \
    --speculative-config '{"method":"dspark","num_speculative_tokens":7,"draft_tensor_parallel_size":4,"attention_backend":"B12X","draft_sample_method":"probabilistic","rejection_sample_method":"standard","enable_adaptive_verification":true}' \
    --reasoning-parser deepseek_v41 \
    --tool-call-parser deepseek_v41 \
    ${API_KEY_ARGS[@]+"${API_KEY_ARGS[@]}"} \
    --enable-auto-tool-choice
