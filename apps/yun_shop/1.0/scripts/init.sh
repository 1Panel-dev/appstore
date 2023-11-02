#!/bin/bash

/usr/bin/dnf install unzip -y
/usr/bin/curl -o data/shop.zip https://downloads.yunzmall.com/framework-yun_shop_free.zip
cd data && unzip shop.zip
/usr/bin/chown -R 9999:9999 ../data
rm -rf shop.zip
