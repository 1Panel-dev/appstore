#!/bin/bash

if [[ -f ./.env ]]; then
  source .env

  if [ ! -f "./data/config/config/network.xml" ]; then
    cp -f ./config/network.xml "./data/config/config/network.xml"
    sed -i "s/{PANEL_APP_PORT_HTTP}/$PANEL_APP_PORT_HTTP/g" "./data/config/config/network.xml"
    sed -i "s/{PANEL_APP_PORT_HTTPS}/$PANEL_APP_PORT_HTTPS/g" "./data/config/config/network.xml"
    sed -i "s/{JELLYFIN_IPV6_ENABLE}/$JELLYFIN_IPV6_ENABLE/g" "./data/config/config/network.xml"
  else
    echo "network.xml already exists."
    sed -i "s/<PublicPort>[0-9]\{1,5\}<\/PublicPort>/<PublicPort>$PANEL_APP_PORT_HTTP<\/PublicPort>/g" "./data/config/config/network.xml"
    sed -i "s/<HttpServerPortNumber>[0-9]\{1,5\}<\/HttpServerPortNumber>/<HttpServerPortNumber>$PANEL_APP_PORT_HTTP<\/HttpServerPortNumber>/g" "./data/config/config/network.xml"
    sed -i "s/<PublicHttpsPort>[0-9]\{1,5\}<\/PublicHttpsPort>/<PublicHttpsPort>$PANEL_APP_PORT_HTTPS<\/PublicHttpsPort>/g" "./data/config/config/network.xml"
    sed -i "s/<HttpsPortNumber>[0-9]\{1,5\}<\/HttpsPortNumber>/<HttpsPortNumber>$PANEL_APP_PORT_HTTPS<\/HttpsPortNumber>/g" "./data/config/config/network.xml"
    sed -i "s/<EnableIPV6>[a-z]\{4,5\}<\/EnableIPV6>/<EnableIPV6>$JELLYFIN_IPV6_ENABLE<\/EnableIPV6>/g" "./data/config/config/network.xml"
  fi

  echo "Check Finish."

else
  echo ".env not found."
fi
