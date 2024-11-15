# Artalk

Artalk 是一款简单易用但功能丰富的评论系统，你可以开箱即用地部署并置入任何博客、网站、Web 应用。

## 创建管理员账号

- 方式一:

  执行命令创建管理员账户（注意第一个`artalk`是你的容器名称）：
    ```bash
    docker exec -it artalk artalk admin
    ```

- 方式二:

  在面板【容器】中找到`artalk`的容器，点击【终端】，然后执行命令：
    ```bash
    artalk admin
    ```
