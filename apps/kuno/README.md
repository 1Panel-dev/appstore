# Kuno

Kuno 是一个轻量级多语言内容管理系统（CMS），采用 Go 后端和 Next.js 前端技术栈，专为创建丰富的内容生态系统而设计。

## 主要特性

- **多语言支持**: 支持 70+ 种语言
- **现代技术栈**: Go 后端 + Next.js 前端
- **响应式设计**: 支持各种屏幕尺寸
- **Markdown 编辑器**: 方便的内容编辑体验
- **深色/浅色模式**: 用户体验友好
- **管理面板**: 完整的后台管理功能
- **分类管理**: 灵活的内容分类系统
- **Docker 部署**: 一键部署，易于维护

## 部署说明

### 默认登录信息

- 用户名: `admin`
- 密码: `xuemian168`

**重要提示**: 首次登录后请立即修改默认密码！

## 维护模式和密码重置

### 恢复模式

如果您忘记了管理员密码，可以通过以下步骤重置：

1. 在 1Panel 应用管理界面中，编辑 Kuno 应用配置
2. 将 "恢复模式" 设置为 "启用"
3. 重启应用容器
4. 使用默认凭据登录：
   - 用户名: `admin`
   - 密码: `xuemian168`
5. 登录后立即修改密码
6. 将 "恢复模式" 重新设置为 "禁用"
7. 再次重启应用容器

**安全提示**: 恢复模式会临时启用默认密码访问，请在重置密码后立即禁用此模式。

### 数据持久化

应用会将所有数据存储在 `/app/data` 目录中，包括：
- SQLite 数据库文件
- 用户上传的文件
- 配置文件

### 环境变量说明

- `NEXT_PUBLIC_API_URL`: API 端点地址
- `DB_PATH`: SQLite 数据库路径 (默认: `/app/data/kuno.db`)
- `GIN_MODE`: Go 后端运行模式 (生产环境建议使用 `release`)
- `NODE_ENV`: Node.js 环境 (生产环境建议使用 `production`)
- `JWT_SECRET`: JWT 认证密钥 (请使用随机字符串)
- `RECOVERY_MODE`: 恢复模式 (默认: `false`，启用时允许使用默认密码登录)

## 使用指南

1. 部署完成后，访问应用地址
2. 使用默认账号登录
3. 立即修改默认密码
4. 开始创建和管理内容

## 系统要求

- CPU: 1 核心
- 内存: 1GB RAM
- 存储: 10GB 可用空间
- 架构: x86_64/amd64

## 故障排除

### 安装一直处于进行中状态

如果应用在1Panel中显示"安装中"且长时间未完成，请按以下步骤排查：

**立即尝试的解决方案**：
1. **刷新页面** - 有时仅仅是界面显示问题
2. **检查网络** - 确保服务器可以访问 Docker Hub
3. **查看系统资源** - 确保有足够的磁盘空间和内存

**如果问题持续存在**：

#### 1. 检查Docker容器状态
```bash
# 查看所有容器状态
docker ps -a

# 查看Kuno相关容器
docker ps -a | grep kuno
```

#### 2. 检查容器日志
```bash
# 查看容器启动日志
docker logs <容器名称或ID>

# 实时查看日志
docker logs -f <容器名称或ID>
```

#### 3. 检查镜像拉取状态
```bash
# 查看镜像拉取进度
docker pull ictrun/kuno:latest

# 查看本地镜像
docker images | grep kuno
```

#### 4. 检查端口占用
```bash
# 检查端口是否被占用
netstat -tlnp | grep <端口号>
# 或使用
ss -tlnp | grep <端口号>
```

#### 5. 检查磁盘空间
```bash
# 检查磁盘使用情况
df -h

# 检查Docker空间使用
docker system df
```

#### 6. 常见解决方案

1. **重新安装应用**：
   - 删除应用
   - 清理残留容器：`docker container prune`
   - 重新从应用商店安装

2. **手动拉取镜像**：
   ```bash
   docker pull ictrun/kuno:latest
   ```

3. **检查网络连接**：
   - 确保服务器可以访问Docker Hub
   - 检查防火墙设置

4. **查看1Panel系统日志**：
   ```bash
   journalctl -u 1panel -f
   ```

5. **重启Docker服务**：
   ```bash
   systemctl restart docker
   ```

6. **手动验证镜像**：
   ```bash
   # 测试镜像是否可用
   docker run --rm ictrun/kuno:latest --version
   ```

7. **使用1Panel命令行工具**：
   ```bash
   # 查看1Panel应用状态
   1pctl app list
   1pctl app logs kuno
   ```

### 应用无法访问

如果应用安装成功但无法访问：

1. **检查容器是否正常运行**
   ```bash
   docker ps | grep kuno
   docker logs <容器名称>
   ```

2. **验证端口配置是否正确**
   ```bash
   netstat -tlnp | grep <端口号>
   ```

3. **检查防火墙是否开放对应端口**
   ```bash
   # CentOS/RHEL
   firewall-cmd --list-ports
   firewall-cmd --add-port=<端口号>/tcp --permanent
   
   # Ubuntu/Debian
   ufw status
   ufw allow <端口号>
   ```

4. **确认容器健康状态**
   ```bash
   docker inspect <容器名称> | grep Health -A 10
   ```

### 常见错误代码对照

- **错误代码 500**: 通常是数据库初始化问题，检查数据目录权限
- **错误代码 502**: 容器未正常启动，查看容器日志
- **连接超时**: 网络或防火墙问题，检查端口开放状态
- **镜像拉取失败**: 网络问题或镜像不存在，尝试手动拉取镜像

## 备份建议

定期备份 `data` 目录以确保数据安全。

## 更多信息

- GitHub: https://github.com/xuemian168/kuno
- 文档: https://github.com/xuemian168/kuno/blob/main/README.md