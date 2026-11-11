FROM lcr.loongnix.cn/library/debian:sid-20250615
RUN printf 'Types: deb\nURIs: http://mirrors.tuna.tsinghua.edu.cn/debian\nSuites: sid\nComponents: main\nSigned-By: /usr/share/keyrings/debian-archive-keyring.gpg\n' > /etc/apt/sources.list.d/debian.sources && rm -f /etc/apt/sources.list
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends apache2 libapache2-mod-php ca-certificates php8.4-gd php8.4-mysql php8.4-curl php8.4-zip && rm -rf /var/lib/apt/lists/*
COPY site /var/www/html/
RUN chown -R www-data:www-data /var/www/html
ENV TZ=Asia/Shanghai
EXPOSE 80
CMD ["sh","-c",". /etc/apache2/envvars && exec apache2 -D FOREGROUND"]
