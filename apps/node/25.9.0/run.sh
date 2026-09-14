#!/bin/bash

source /.env

if [[ "$PACKAGE_MANAGER" == "npm" ]]; then
    npm config set registry $CONTAINER_PACKAGE_URL
elif [[ "$PACKAGE_MANAGER" == "yarn" ]]; then
    yarn config set registry $CONTAINER_PACKAGE_URL
elif [[ "$PACKAGE_MANAGER" == "pnpm" ]]; then
    pnpm config set registry $CONTAINER_PACKAGE_URL
fi

if [[ "$RUN_INSTALL" -eq "1" ]]; then
    if [[ "$PACKAGE_MANAGER" == "npm" ]]; then
        npm install
    elif [[ "$PACKAGE_MANAGER" == "yarn" ]]; then
        yarn install
    elif [[ "$PACKAGE_MANAGER" == "pnpm" ]]; then
        pnpm install    
    else
        echo "未知的 PACKAGE_MANAGER: $PACKAGE_MANAGER"
        exit 1
    fi
fi


if [[ "$CUSTOM_SCRIPT" -eq "1" ]]; then
  eval $EXEC_SCRIPT
else
  if [[ "$PACKAGE_MANAGER" == "npm" ]]; then
      npm run $EXEC_SCRIPT
  elif [[ "$PACKAGE_MANAGER" == "yarn" ]]; then
      yarn run $EXEC_SCRIPT
  elif [[ "$PACKAGE_MANAGER" == "pnpm" ]]; then
      pnpm run $EXEC_SCRIPT    
  fi
fi

