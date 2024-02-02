#!/bin/bash

# 检查 .env 文件是否存在
if [ -f .env ]; then
  # 导入 .env 文件中的变量
  source .env

  # 检查模板是否启用
  if [ "$MS_TEMPLATE_ENABLED" = "true" ]; then
      # 检查模板文件是否存在
      if [ -e "docker-compose-template.yml" ]; then
          # 读取模板文件的内容
          template_content=$(<docker-compose-template.yml)
          # 清空目标文件
          > docker-compose.yml
          # 将模板内容写入目标文件
          echo "$template_content" > docker-compose.yml

          echo "docker-compose.yml updated successfully."
      else
          echo "Error: docker-compose-template.yml not found."
          exit 1
      fi
  fi

  # 替换 docker-compose.yml 中的网络变量
  sed -i "s/\${DOCKER_NET}/$MICROSERVICES_NETWORK/" docker-compose.yml

  # 创建网络
  docker network create "$MICROSERVICES_NETWORK"
  # 检查创建是否成功
  if [ $? -eq 0 ]; then
      echo "Network $MICROSERVICES_NETWORK created successfully."
  else
      echo "Failed to create network $MICROSERVICES_NETWORK."
  fi

  echo "Directories and permissions set successfully."

else
  echo "Error: .env file not found."
  exit 1
fi
