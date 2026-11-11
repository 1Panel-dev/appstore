# loong64 self-built (wave3)
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata 2>/dev/null || (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata)
WORKDIR /app
COPY prometheus /app/prometheus
COPY prometheus.yml /etc/prometheus/prometheus.yml
RUN chmod +x /app/prometheus && mkdir -p /prometheus
ENV TZ=Asia/Shanghai
EXPOSE 9090
ENTRYPOINT ["/app/prometheus"]
CMD ["--config.file=/etc/prometheus/prometheus.yml", "--storage.tsdb.path=/prometheus"]
