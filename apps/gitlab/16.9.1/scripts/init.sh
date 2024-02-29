#!/bin/bash

# 检查 .env 文件是否存在
if [ -f .env ]; then
  # 导入 .env 文件中的变量
  source .env

  # 创建目录
  mkdir -p "$GITLAB_HOME"

  mkdir -p "$GITLAB_HOME/data"
  mkdir -p "$GITLAB_HOME/config"
  mkdir -p "$GITLAB_HOME/logs"

  echo "Directories set successfully."

else
  echo "Error: .env file not found."
  exit 1
fi
