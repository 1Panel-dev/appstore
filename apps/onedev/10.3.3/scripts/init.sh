#!/bin/bash

echo "Initializing OneDev..."

if [ -f .env ]; then
  source .env

  echo "Setting up environment variables..."

  if [ "$ENABLE_DATABASE" = "true" ]; then
    if [ -z "$DATABASE_TYPE" ] || [ -z "$DATABASE_HOST" ] || [ -z "$DATABASE_PORT" ] || [ -z "$DATABASE_USER" ] || [ -z "$DATABASE_PASSWORD" ] || [ -z "$DATABASE_NAME" ]; then
      echo "Error: Database environment variables not found."
      exit 1
    fi
    # 写入到 .env
    # 判断 DATABASE_TYPE 写入对应值
    if [ "$DATABASE_TYPE" = "MySQL" ]; then
      echo "hibernate_dialect=org.hibernate.dialect.MySQL5InnoDBDialect" >> .env
      echo "hibernate_connection_driver_class=com.mysql.cj.jdbc.Driver" >> .env
      echo "hibernate_connection_url=jdbc:mysql://$DATABASE_HOST:$DATABASE_PORT/$DATABASE_NAME?serverTimezone=UTC&allowPublicKeyRetrieval=true&useSSL=false&disableMariaDbDriver=true" >> .env
    elif [ "$DATABASE_TYPE" = "PostgreSQL" ]; then
      echo "hibernate_dialect=io.onedev.server.persistence.PostgreSQLDialect" >> .env
      echo "hibernate_connection_driver_class=org.postgresql.Driver" >> .env
      echo "hibernate_connection_url=jdbc:postgresql://$DATABASE_HOST:$DATABASE_PORT/$DATABASE_NAME" >> .env
    elif [ "$DATABASE_TYPE" = "MariaDB" ]; then
      echo "hibernate_dialect=org.hibernate.dialect.MySQL5InnoDBDialect" >> .env
      echo "hibernate_connection_driver_class=org.mariadb.jdbc.Driver" >> .env
      echo "hibernate_connection_url=jdbc:mariadb://$DATABASE_HOST:$DATABASE_PORT/$DATABASE_NAME" >> .env
    elif [ "$DATABASE_TYPE" = "MS SQL Server" ]; then
      echo "hibernate_dialect=org.hibernate.dialect.SQLServer2012Dialect" >> .env
      echo "hibernate_connection_driver_class=com.microsoft.sqlserver.jdbc.SQLServerDriver" >> .env
      echo "hibernate_connection_url=jdbc:sqlserver://$DATABASE_HOST:$DATABASE_PORT;databaseName=$DATABASE_NAME" >> .env
    else
      echo "Error: Database type not supported."
      exit 1
    fi
    echo "hibernate_connection_username=$DATABASE_USER" >> .env
    echo "hibernate_connection_password=$DATABASE_PASSWORD" >> .env
  else
    sed -i '/hibernate_dialect/d' .env
    sed -i '/hibernate_connection_driver_class/d' .env
    sed -i '/hibernate_connection_url/d' .env
    sed -i '/hibernate_connection_username/d' .env
    sed -i '/hibernate_connection_password/d' .env
  fi

  echo "Starting OneDev..."

else
  echo "Error: .env file not found."
  exit 1
fi
