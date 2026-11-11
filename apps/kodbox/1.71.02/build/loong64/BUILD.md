# kodbox 1.71.02 — loong64 构建配方

- 上游仓库: https://github.com/kalcaddle/kodbox
- 版本/tag: `1.71.02`
- git tag: `1.71.03`（上游 tag 命名与版本号不一致时以此为准）
- 路线: static（PHP/静态站点）

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
FROM lcr.loongnix.cn/library/debian:sid-20250615
RUN printf 'Types: deb\nURIs: http://mirrors.tuna.tsinghua.edu.cn/debian\nSuites: sid\nComponents: main\nSigned-By: /usr/share/keyrings/debian-archive-keyring.gpg\n' > /etc/apt/sources.list.d/debian.sources && rm -f /etc/apt/sources.list
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends apache2 libapache2-mod-php ca-certificates php8.4-gd php8.4-mysql php8.4-curl php8.4-zip && rm -rf /var/lib/apt/lists/*
COPY site /var/www/html/
RUN chown -R www-data:www-data /var/www/html
ENV TZ=Asia/Shanghai
EXPOSE 80
CMD ["sh","-c",". /etc/apache2/envvars && exec apache2 -D FOREGROUND"]
```

## 烟囱测试

- 类型: http，等待 6s，HTTP GET /
