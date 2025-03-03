`安装时可根据需要编辑 Compose 文件，调整参数`

```
默认禁止修改管理员账号和密码，完成部署后，请按照以下步骤操作：
1. 进入 "参数" -> "编辑" -> "高级设置"
2. 修改 Compose 文件，删除以下行：

- DEFAULT_ADMIN_EMAIL=demo@demo.demo 
- DEFAULT_ADMIN_PASSWORD=demo
- DEFAULT_ADMIN_NAME=Demo Demo
- DEFAULT_ADMIN_USERNAME=demo

3. 重建后登陆修改
```

# Planka 📌  

**Planka** 是一款开源、自托管的项目管理工具，类似 Trello，基于 **Node.js、React 和 PostgreSQL** 构建。  

## 🌟 主要特性  
- 📌 **可视化看板** —— 拖拽操作直观，管理任务更高效  
- 👥 **团队协作** —— 支持多用户，灵活权限管理  
- 🔄 **实时同步** —— WebSockets 确保多端数据即时更新  
- 🚀 **轻量高效** —— 运行流畅，占用资源少  
