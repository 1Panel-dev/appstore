#!/bin/bash

# 检查 .env 文件是否存在
if [ -f .env ]; then
  # 导入 .env 文件中的变量
  source .env

  # 使用 docker network rm 命令删除网络
  docker network rm $CLUSTER_NETWORK

  # 检查删除是否成功
  if [ $? -eq 0 ]; then
      echo "Network $CLUSTER_NETWORK deleted successfully."
  else
      echo "Failed to delete network $CLUSTER_NETWORK."
  fi

else
  echo "Error: .env file not found."
  exit 1
fi
