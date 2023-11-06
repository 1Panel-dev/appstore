# 创建邮箱账户

点击左侧 `容器` 菜单，点击右侧 `终端` 按钮，执行以下命令来创建邮箱账户:

```shell
// 创建邮箱账户: docker exec -ti <CONTAINER NAME> setup email add <NEW ADDRESS>

setup email add admin@example.com
```

# Mailserver

Mailserver 是一个生产就绪的全栈但简单的容器化邮件服务器（SMTP、IMAP、LDAP、反垃圾邮件、防病毒等）。只有配置文件，没有SQL数据库。保持简单和版本化。易于部署和升级。