# loong64 self-built (wave7) - store-compatible entrypoint (env -> conf/app.conf)
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata 2>/dev/null || (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata)
WORKDIR /app
COPY casdoor /app/casdoor
COPY conf /app/conf
COPY web/build /app/web/build
COPY entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/casdoor /app/entrypoint.sh
ENV TZ=Asia/Shanghai
EXPOSE 8000
ENTRYPOINT ["/bin/sh", "/app/entrypoint.sh"]