## 使用说明

1. 注册账户
2. 进入容器执行以下命令

```bash
python ./chrole.py your@email.address admin
```

> 获得完整管理员权限，需要先退出再登录系统。

## 产品介绍

**QD** 是 一个 基于 HAR 编辑器和 Tornado 服务端的 HTTP 定时任务自动执行 Web 框架。

## 特性

- **基于Har**：仅需上传通过抓包得到的 Har, 即可制作框架所需的 HTTP 任务模板。
- **Tornado 服务端**：使用 Tornado 作为服务端, 以实现异步响应前端和发起 HTTP 请求。
- **API & 插件支持**：内置多种 API 和过滤器用于模板制作, 后续将提供自定义插件支持。
- **开源**：QD 是一个基于 MIT 许可证的开源项目。
