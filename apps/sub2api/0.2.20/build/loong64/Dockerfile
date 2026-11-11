# loong64 self-built (wave27) - embed release build per upstream Dockerfile
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata libpq 2>/dev/null || (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata libpq)
WORKDIR /app
COPY sub2api /app/sub2api
COPY resources /app/resources
RUN chmod +x /app/sub2api && mkdir -p /app/data
ENV TZ=Asia/Shanghai
EXPOSE 8080
ENTRYPOINT ["/app/sub2api"]
