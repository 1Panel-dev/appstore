#!/bin/bash

if [[ -f ./.env ]]; then
  source .env

  mkdir -p "$EMBY_ROOT_PATH"

  mkdir -p "$EMBY_ROOT_PATH/config"
  mkdir -p "$EMBY_ROOT_PATH/config/config"
  mkdir -p "$EMBY_ROOT_PATH/mnt"

  if [ ! -f "$EMBY_ROOT_PATH/config/config/system.xml" ]; then
    cp -f ./config/system.xml "$EMBY_ROOT_PATH/config/config/system.xml"
    sed -i "s/{PANEL_APP_PORT_HTTP}/$PANEL_APP_PORT_HTTP/g" "$EMBY_ROOT_PATH/config/config/system.xml"
    sed -i "s/{PANEL_APP_PORT_HTTPS}/$PANEL_APP_PORT_HTTPS/g" "$EMBY_ROOT_PATH/config/config/system.xml"
  else
    echo "system.xml already exists."
    sed -i "s/<PublicPort>[0-9]\{1,5\}<\/PublicPort>/<PublicPort>$PANEL_APP_PORT_HTTP<\/PublicPort>/g" "$EMBY_ROOT_PATH/config/config/system.xml"
    sed -i "s/<HttpServerPortNumber>[0-9]\{1,5\}<\/HttpServerPortNumber>/<HttpServerPortNumber>$PANEL_APP_PORT_HTTP<\/HttpServerPortNumber>/g" "$EMBY_ROOT_PATH/config/config/system.xml"
    sed -i "s/<PublicHttpsPort>[0-9]\{1,5\}<\/PublicHttpsPort>/<PublicHttpsPort>$PANEL_APP_PORT_HTTPS<\/PublicHttpsPort>/g" "$EMBY_ROOT_PATH/config/config/system.xml"
    sed -i "s/<HttpsPortNumber>[0-9]\{1,5\}<\/HttpsPortNumber>/<HttpsPortNumber>$PANEL_APP_PORT_HTTPS<\/HttpsPortNumber>/g" "$EMBY_ROOT_PATH/config/config/system.xml"
  fi

  chmod -R $UID:$GID "$EMBY_ROOT_PATH"

  echo "Check Finish."

else
  echo ".env not found."
fi
