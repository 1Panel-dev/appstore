#!/bin/sh

echo
echo "============================================"
echo "Install extensions from   : install.sh"
echo "PHP version               : ${PHP_VERSION}"
echo "Extra Extensions          : ${PHP_EXTENSIONS}"
echo "Multicore Compilation     : ${MC}"
echo "Container package url     : ${CONTAINER_PACKAGE_URL}"
echo "Work directory            : ${PWD}"
echo "============================================"
echo

IFS=','
for ext in $PHP_EXTENSIONS; do
    echo "开始安装 $ext..."
    install-php-extensions $ext

    if [ $? -eq 0 ]; then
        echo "$ext 安装成功！"
    else
        echo "$ext 安装失败！"
    fi
done
unset IFS
