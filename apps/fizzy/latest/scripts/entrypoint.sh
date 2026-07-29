#!/bin/sh

if [ "${SMTP_USERNAME-}" = "" ]; then
  unset SMTP_USERNAME
fi

if [ "${SMTP_PASSWORD-}" = "" ]; then
  unset SMTP_PASSWORD
fi

exec /rails/bin/docker-entrypoint "$@"
