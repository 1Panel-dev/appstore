## 注意事项

- 使用端口访问会导致控制台无法显示，请修改为使用域名访问。

## 配置

### 设置语言

在浏览器中访问 URI `/admin/site_settings/category/required`，将 `default locale` 设置为 `简体中文` 即可。

### 设置 SMTP

安装应用时手动编辑 `docker-compose`，修改 SMTP 相关配置即可。

### 插件安装

**进入 `容器` 页面，找到 Discourse 应用，点击右侧操作列中的 `终端` 按钮，在容器终端中执行以下命令：**

```shell
cd /opt/bitnami/discourse

# 安装插件 PLUGIN_REPO_URL 替换为插件地址
sudo RAILS_ENV=production bundle exec rake plugin:install repo=PLUGIN_REPO_URL

# 预编译新资源以供插件使用
sudo RAILS_ENV=production bundle exec rake assets:precompile
```

### 插件卸载

**进入 `容器` 页面，找到 Discourse 应用，点击右侧操作列中的 `终端` 按钮，在容器终端中执行以下命令：**

```shell
cd /opt/bitnami/discourse/plugins

# 删除插件目录 PLUGIN-DIR 替换为插件目录
rm -rf PLUGIN-DIR

# 预编译新资源
sudo RAILS_ENV=production bundle exec rake assets:precompile
```