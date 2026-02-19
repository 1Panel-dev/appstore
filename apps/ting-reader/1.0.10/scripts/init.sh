#!/bin/bash

# 1Panel 应用初始化脚本
# 在应用启动前执行

# 定义日志文件
LOG_FILE="init.log"

echo "Starting initialization..." > $LOG_FILE

# 1. 生成高强度 JWT 密钥 (64位)
if [ -f ".env" ]; then
    echo "Updating JWT_SECRET in .env..." >> $LOG_FILE
    
    # 检查 JWT_SECRET 是否已存在
    if grep -q "JWT_SECRET=" .env; then
        # 生成 64 位随机字母数字组合
        NEW_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 64 | head -n 1)
        # 替换 .env 中的 JWT_SECRET
        sed -i "s|^JWT_SECRET=.*|JWT_SECRET=${NEW_SECRET}|" .env
        echo "JWT_SECRET updated to a 64-character secure string." >> $LOG_FILE
    else
        # 如果不存在（因为 data.yml 没生成），则追加
        NEW_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 64 | head -n 1)
        echo "" >> .env
        echo "JWT_SECRET=${NEW_SECRET}" >> .env
        echo "JWT_SECRET appended to .env." >> $LOG_FILE
    fi
else
    echo ".env file not found!" >> $LOG_FILE
fi

echo "Initialization completed." >> $LOG_FILE
