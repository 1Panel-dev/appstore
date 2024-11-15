# Artalk

**Artalk** 是一款简单易用但功能丰富的评论系统，你可以开箱即用地部署并置入任何博客、网站、Web 应用。

## 创建管理员账号

- 方式一:

  在【容器】页面找到 `artalk` 容器，点击右侧的【终端】按钮后，执行以下命令：

  ```bash
  artalk admin
  ```

- 方式二:

  在【终端】页面执行命令创建管理员账户（注意，第一个 `artalk` 是你的容器名称）：
 
  ```bash
  docker exec -it artalk artalk admin
  ```
