# 注意事项
- 同一个存储卷，只会在首次初始化管理员账号
- 使用端口访问的话，不会显示图标，修改为域名即可

# 配置
## 设置语言
/admin/site_settings/category/required

default locale 设置为 简体中文

## 设置SMTP
手动编辑 docker-compose，修改 smtp相关配置


## 插件安装
```shell
cd /opt/bitnami/discourse
# 安装插件 PLUGIN_REPO_URL 替换为插件地址
sudo RAILS_ENV=production bundle exec rake plugin:install repo=PLUGIN_REPO_URL
# 最后，预编译新资源以供插件使用:
sudo RAILS_ENV=production bundle exec rake assets:precompile
```

## 插件卸载
```shell
cd /opt/bitnami/discourse/plugins
# 删除插件目录 PLUGIN-DIR 替换为插件目录
rm -rf PLUGIN-DIR
# 最后，预编译新资源
sudo RAILS_ENV=production bundle exec rake assets:precompile
```

# Discourse

Discourse是一个现代化的讨论平台，提供了以下令人印象深刻的功能：

## 主要功能：

- **无缝滚动对话**：摒弃传统的分页，实现即时加载，用户可以无限滚动阅读更多内容。
- **实时聊天**：创建社区成员之间的非正式聊天渠道，建立关系，并将聊天信息引用到主题中以持续讨论。
- **个性化体验**：自定义侧边栏和用户偏好设置，允许社区成员根据个人需求调整体验。
- **简洁且有上下文**：Discourse是一个简单的平面论坛，回复按页面顺序排列，通过展开帖子上下文和引用来展示完整对话。
- **移动优先设计**：为高分辨率触摸设备设计，内置移动布局，支持iOS和Android应用。
- **自动扩展链接**：粘贴链接即可自动扩展，提供来自Wikipedia、YouTube等数百个流行网站的额外上下文和信息。
- **单点登录**：与现有网站的登录系统集成，实现简单、强大的单点登录。
- **信任系统**：随着成员逐渐成为受信任的常客，他们将获得帮助维护社区的能力。
- **社区管理**：让社区自行抑制垃圾邮件和危险内容，友好解决争议。
- **垃圾邮件屏蔽**：内置Akismet垃圾邮件保护和启发式规则，包括新用户沙盒、用户标志阻止和标准nofollow。
- **社交登录**：轻松添加Google、Facebook、Twitter、Discord和GitHub等常见社交登录。
- **主题摘要**：使用摘要按钮将长主题压缩为最有趣和最受欢迎的帖子。
- **徽章系统**：通过包含的徽章集鼓励积极的社区行为，或添加自定义徽章。
- **表情符号**：提供可搜索的标准表情符号列表，可选择四种不同的表情符号集或定义自定义表情符号。
- **通过电子邮件回复**：当用户不活跃在网站上时，通知将自动通过电子邮件发送，并可从任何地方、任何设备通过电子邮件回复。
- **双因素认证**：使用免费的Android或iOS认证应用程序增强账户安全性。
- **管理仪表板**：社区健康指标最相关和基本的指标只需点击即可访问。
- **全面API**：屏幕上看到的任何内容，也可以通过API调用完成。
- **100%开源**：完全开放的代码，可以完全信任地将Discourse集成到你的站点中。
- **一键更新**：通过一键式网络更新流程，在仪表板上自动通知新版本。
