#!/bin/bash

DIR="./data/"

if [ -d "$DIR" ]; then
  echo "目录 $DIR 已存在"
else
  echo "目录 $DIR 不存在，正在创建..."
  mkdir -p "$DIR"
  if [ $? -eq 0 ]; then
    echo "目录 $DIR 创建成功"
  else
    echo "目录 $DIR 创建失败"
  fi
fi

chown -R 1001:1001 $DIR