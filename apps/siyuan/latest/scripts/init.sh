#!/bin/bash

# 检查并加载环境变量
if [ -f .env ]; then
  source .env
else
  echo ".env file not found. Please create one with the WORKSPACE_PATH variable defined."
  exit 1
fi

# 判断 WORKSPACE_PATH 变量是否被设置
if [ -z "${WORKSPACE_PATH}" ]; then
  echo "WORKSPACE_PATH is not set."
  exit 1
fi

# 检查 WORKSPACE_PATH 指定的文件夹是否存在，不存在则创建
if [ ! -d "${WORKSPACE_PATH}" ]; then
  echo "Creating workspace directory at ${WORKSPACE_PATH}"
  mkdir -p "${WORKSPACE_PATH}"
fi

# 修改 WORKSPACE_PATH 文件夹及其内容的所有者为用户ID 1000 和组ID 1000
echo "Setting ownership of ${WORKSPACE_PATH} to user 1000 and group 1000"
chown -R 1000:1000 "${WORKSPACE_PATH}" 2>/dev/null

# 检查是否成功设置了所有权
if [ $? -eq 0 ]; then
  echo "Ownership set successfully."
else
  echo "Failed to set ownership."
  exit 1
fi

exit 0
