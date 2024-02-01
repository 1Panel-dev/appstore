#!/bin/bash

# 检查 .env 文件是否存在
if [ -f .env ]; then
  # 导入 .env 文件中的变量
  source .env

  # 替换 docker-compose.yml 中的网络变量
  sed -i "s/\${DOCKER_NET}/$CLUSTER_NETWORK/" docker-compose.yml

  # 创建并设置权限
  mkdir -p "$ES_ROOT_PATH"

  mkdir -p "$ES_ROOT_PATH/$ES_NODE_NAME/data"
  mkdir -p "$ES_ROOT_PATH/$ES_NODE_NAME/logs"
  mkdir -p "$ES_ROOT_PATH/$ES_NODE_NAME/config"
  mkdir -p "$ES_ROOT_PATH/$ES_NODE_NAME/plugins"

  # 生成 elasticsearch.yml 文件
  elasticsearch_config="cluster.name: \"$CLUSTER_NAME\"\nnetwork.host: 0.0.0.0"
  echo -e "$elasticsearch_config" > elasticsearch.yml
  cp elasticsearch.yml "$ES_ROOT_PATH/$ES_NODE_NAME/config/elasticsearch.yml"

  chmod -R 777 "$ES_ROOT_PATH"

  echo "Directories and permissions set successfully."

else
  echo "Error: .env file not found."
  exit 1
fi
