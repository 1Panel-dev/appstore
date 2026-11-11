FROM lcr.loongnix.cn/library/eclipse-temurin:21-jdk
RUN apt-get update && apt-get install -y --no-install-recommends unzip && rm -rf /var/lib/apt/lists/*
COPY keycloak /opt/keycloak
RUN chmod -R +x /opt/keycloak/bin/*.sh
WORKDIR /opt/keycloak
EXPOSE 8080
ENTRYPOINT ["/opt/keycloak/bin/kc.sh"]
CMD ["start-dev"]
