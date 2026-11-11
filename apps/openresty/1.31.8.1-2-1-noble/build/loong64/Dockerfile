# Dockerfile.loong64 - OpenResty 1.31.8.1 for LoongArch64 (Loongnix)
# Based on 1Panel-dev/appstore apps/openresty/1.31.8.1-2-1-noble/build/Dockerfile
# Changes for loong64:
#   1. base image ubuntu:noble -> lcr.loongnix.cn/library/debian:sid (apt-compatible)
#   2. drop the "FROM 1panel/openresty" bootstrap stage: brotli module, 1pwaf
#      libs (libmaxminddb/libinjection), lsqlite3, lfs are built from vendored
#      source inside this image
#   3. 1pwaf lua engine COPYed from build context (extracted from official
#      amd64 image, lua/data are arch-independent)
#   4. apt mirror sed adapted for debian sources (deb822 debian.sources)

ARG RESTY_IMAGE_BASE="lcr.loongnix.cn/library/debian"
ARG RESTY_IMAGE_TAG="sid-20250615"

FROM ${RESTY_IMAGE_BASE}:${RESTY_IMAGE_TAG}

LABEL maintainer="loong64 port for 1Panel"

ARG RESTY_IMAGE_BASE="lcr.loongnix.cn/library/debian"
ARG RESTY_IMAGE_TAG="sid-20250615"
ARG RESTY_VERSION="1.31.8.1"
ARG RESTY_LUAROCKS_VERSION="3.13.0"

ARG RESTY_OPENSSL_VERSION="3.5.9"
ARG RESTY_OPENSSL_PATCH_VERSION="3.5.5"
# vendored in tmp/, kept as ARG for parity with upstream recipe
ARG RESTY_OPENSSL_URL_BASE="https://github.com/openssl/openssl/releases/download/openssl-${RESTY_OPENSSL_VERSION}"
ARG RESTY_OPENSSL_BUILD_OPTIONS="enable-camellia enable-seed enable-rfc3779 enable-cms enable-md2 enable-rc5 \
        enable-weak-ssl-ciphers enable-ssl3 enable-ssl3-method enable-md2 enable-ktls enable-fips \
        "

ARG RESTY_PCRE_VERSION="10.49"
ARG RESTY_PCRE_SHA256="929f0b20e62879252a15886b06c89f1edef61a363cbd5826fb041080a5e557ae"
ARG RESTY_PCRE_BUILD_OPTIONS="--enable-jit --enable-pcre2grep-jit --disable-bsr-anycrlf --disable-coverage --disable-ebcdic --disable-fuzz-support \
    --disable-jit-sealloc --disable-never-backslash-C --enable-newline-is-lf --enable-pcre2-8 --enable-pcre2-16 --enable-pcre2-32 \
    --enable-pcre2grep-callout --enable-pcre2grep-callout-fork --disable-pcre2grep-libbz2 --disable-pcre2grep-libz --disable-pcre2test-libedit \
    --enable-percent-zt --disable-rebuild-chartables --enable-shared --disable-static --disable-silent-rules --enable-unicode --disable-valgrind \
    "

ARG RESTY_J="1"

ARG RESTY_CONFIG_OPTIONS="\
    --with-compat \
    --without-http_rds_json_module \
    --without-http_rds_csv_module \
    --without-lua_rds_parser \
    --without-mail_pop3_module \
    --without-mail_imap_module \
    --without-mail_smtp_module \
    --with-http_addition_module \
    --with-http_auth_request_module \
    --with-http_dav_module \
    --with-http_flv_module \
    --with-http_geoip_module=dynamic \
    --with-http_gunzip_module \
    --with-http_gzip_static_module \
    --with-http_image_filter_module=dynamic \
    --with-http_mp4_module \
    --with-http_random_index_module \
    --with-http_realip_module \
    --with-http_secure_link_module \
    --with-http_slice_module \
    --with-http_ssl_module \
    --with-http_stub_status_module \
    --with-http_sub_module \
    --with-http_v2_module \
    --with-http_v3_module \
    --with-http_xslt_module=dynamic \
    --with-ipv6 \
    --with-mail \
    --with-mail_ssl_module \
    --with-md5-asm \
    --with-sha1-asm \
    --with-stream \
    --with-stream_ssl_module \
    --with-stream_ssl_preread_module \
    --with-threads \
    "
ARG RESTY_CONFIG_OPTIONS_MORE=""
ARG RESTY_LUAJIT_OPTIONS="--with-luajit-xcflags='-DLUAJIT_NUMMODE=2 -DLUAJIT_ENABLE_LUA52COMPAT'"
ARG RESTY_PCRE_OPTIONS="--with-pcre-jit"

ARG RESTY_ADD_PACKAGE_BUILDDEPS=""
ARG RESTY_ADD_PACKAGE_RUNDEPS=""

ARG _RESTY_CONFIG_DEPS="--with-pcre \
    --with-cc-opt='-DNGX_LUA_ABORT_AT_PANIC -I/usr/local/openresty/pcre2/include -I/usr/local/openresty/openssl3/include' \
    --with-ld-opt='-L/usr/local/openresty/pcre2/lib -L/usr/local/openresty/openssl3/lib -Wl,-rpath,/usr/local/openresty/pcre2/lib:/usr/local/openresty/openssl3/lib' \
    "

LABEL resty_image_base="${RESTY_IMAGE_BASE}"
LABEL resty_image_tag="${RESTY_IMAGE_TAG}"
LABEL resty_version="${RESTY_VERSION}"

# note: debian-ports (loong64 sid archive) is NOT mirrored by CN mirrors,
# keep official http://deb.debian.org (Fastly CDN, reachable from CN)
# loong64 packages moved from debian-ports into the official debian archive
# (Debian 13+): rewrite apt sources to official sid via CN http mirror.
# The lcr base image's original debian-ports sources return 404 for loong64.
RUN printf 'Types: deb\nURIs: http://mirrors.tuna.tsinghua.edu.cn/debian\nSuites: sid\nComponents: main\nSigned-By: /usr/share/keyrings/debian-archive-keyring.gpg\n' > /etc/apt/sources.list.d/debian.sources \
    && rm -f /etc/apt/sources.list

COPY tmp /tmp
COPY vendor /tmp/vendor
COPY 1pwaf /usr/local/openresty/1pwaf

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        build-essential \
        ca-certificates \
        curl \
        gettext-base \
        libbrotli-dev \
        libgd-dev \
        libgeoip-dev \
        libncurses5-dev \
        libperl-dev \
        libpcre2-dev \
        libreadline-dev \
        libsqlite3-dev \
        libssl-dev \
        libxslt1-dev \
        make \
        perl \
        unzip \
        wget \
        zlib1g-dev \
        git \
        ${RESTY_ADD_PACKAGE_BUILDDEPS} \
        ${RESTY_ADD_PACKAGE_RUNDEPS}

# snapshot vendored sources to a path nothing else touches (/tmp/vendor has
# proven to disappear between stages on this builder)
RUN cp -r /tmp/vendor /usr/local/build-vendor \
    && ls /usr/local/build-vendor/libinjection/src/libinjection_sqli.c

RUN set -x \
    && cd /tmp \
    && tar xzf openssl-${RESTY_OPENSSL_VERSION}.tar.gz \
    && cd openssl-${RESTY_OPENSSL_VERSION} \
    && patch -p1 < /tmp/openssl-${RESTY_OPENSSL_PATCH_VERSION}-sess_set_get_cb_yield.patch \
    && ./config \
      shared zlib -g \
      --prefix=/usr/local/openresty/openssl3 \
      --libdir=lib \
      -Wl,-rpath,/usr/local/openresty/openssl3/lib \
      ${RESTY_OPENSSL_BUILD_OPTIONS} \
    && make -j${RESTY_J} \
    && make -j${RESTY_J} install_sw \
    && rm -rf /tmp/openssl-${RESTY_OPENSSL_VERSION}

RUN set -x \
    && cd /tmp \
    && echo "${RESTY_PCRE_SHA256}  pcre2-${RESTY_PCRE_VERSION}.tar.gz" | shasum -a 256 --check \
    && tar xzf pcre2-${RESTY_PCRE_VERSION}.tar.gz \
    && cd /tmp/pcre2-${RESTY_PCRE_VERSION} \
    && CFLAGS="-g -O3" ./configure \
        --prefix=/usr/local/openresty/pcre2 \
        --libdir=/usr/local/openresty/pcre2/lib \
        ${RESTY_PCRE_BUILD_OPTIONS} \
    && CFLAGS="-g -O3" make -j${RESTY_J} \
    && CFLAGS="-g -O3" make -j${RESTY_J} install \
    && rm -rf /tmp/pcre2-${RESTY_PCRE_VERSION} /tmp/pcre2-${RESTY_PCRE_VERSION}.tar.gz

RUN set -x \
    && cd /tmp \
    && if [ ! -d openresty-${RESTY_VERSION} ]; then tar xzf openresty-${RESTY_VERSION}.tar.gz; fi \
    && echo '== replacing bundled LuaJIT with loongarch64 port (loongson/LuaJIT v2.1-loongarch64) ==' \
    && rm -rf /tmp/openresty-${RESTY_VERSION}/bundle/LuaJIT-* \
    && cp -r /tmp/vendor/LuaJIT /tmp/openresty-${RESTY_VERSION}/bundle/LuaJIT-2.1-loongarch64 \
    && cd /tmp/openresty-${RESTY_VERSION} \
    && eval ./configure -j${RESTY_J} ${_RESTY_CONFIG_DEPS} ${RESTY_CONFIG_OPTIONS} ${RESTY_CONFIG_OPTIONS_MORE} ${RESTY_LUAJIT_OPTIONS} ${RESTY_PCRE_OPTIONS} \
    && make -j${RESTY_J} \
    && make -j${RESTY_J} install \
    && rm -rf /tmp/openresty-${RESTY_VERSION}.tar.gz

RUN set -x \
    && cd /tmp \
    && tar xzf luarocks-${RESTY_LUAROCKS_VERSION}.tar.gz \
    && cd luarocks-${RESTY_LUAROCKS_VERSION} \
    && ./configure \
        --prefix=/usr/local/openresty/luajit \
        --with-lua=/usr/local/openresty/luajit \
        --with-lua-include=/usr/local/openresty/luajit/include/luajit-2.1 \
    && make build \
    && make install \
    && rm -rf /tmp/luarocks-${RESTY_LUAROCKS_VERSION} /tmp/luarocks-${RESTY_LUAROCKS_VERSION}.tar.gz

# ngx_brotli dynamic module: vendored source placed where the panel's module
# manager (module.catalog.json) expects it, compiled against the openresty tree
RUN set -x \
    && echo '== building ngx_brotli dynamic module ==' \
    && mkdir -p /usr/local/openresty/modules \
    && rm -rf /usr/local/openresty/modules/ngx_brotli \
    && cp -r /usr/local/build-vendor/ngx_brotli /usr/local/openresty/modules/ngx_brotli \
    && cd /tmp \
    && if [ ! -d openresty-${RESTY_VERSION} ]; then tar xzf openresty-${RESTY_VERSION}.tar.gz; fi \
    && cp -r /usr/local/build-vendor/LuaJIT /tmp/openresty-${RESTY_VERSION}/bundle/LuaJIT-2.1-loongarch64 \
    && cd /tmp/openresty-${RESTY_VERSION} \
    && ./configure -j${RESTY_J} --with-compat --with-http_ssl_module --with-stream --with-stream_ssl_module \
         --add-dynamic-module=/usr/local/openresty/modules/ngx_brotli \
    && NGINX_BUILD_DIR="$(find build -mindepth 1 -maxdepth 1 -type d -name 'nginx-*' | head -n 1)" \
    && make -C "${NGINX_BUILD_DIR}" -j${RESTY_J} modules \
    && find "${NGINX_BUILD_DIR}/objs" -maxdepth 1 -type f -name '*.so' -exec cp -a {} /usr/local/openresty/modules/ngx_brotli/ \; \
    && ls -la /usr/local/openresty/modules/ngx_brotli/

# 1pwaf native libs: libmaxminddb (vendored release) + libinjection (vendored src)
RUN set -x \
    && echo '== building 1pwaf native libs ==' \
    && ls -la /usr/local/build-vendor/libinjection/src/libinjection_sqli.c \
    && tar xzf /tmp/libmaxminddb.tar.gz -C /tmp \
    && cd /tmp/libmaxminddb \
    && ./configure --prefix=/tmp/mmdb-install --disable-static \
    && make -j${RESTY_J} \
    && make install \
    && cp -a /tmp/mmdb-install/lib/libmaxminddb.so* /usr/local/openresty/1pwaf/libraries/ \
    && gcc -O2 -fPIC -shared -o /usr/local/openresty/1pwaf/libraries/libinjection.so \
         /usr/local/build-vendor/libinjection/src/libinjection_sqli.c -I/usr/local/build-vendor/libinjection/src \
    && ls -la /usr/local/openresty/1pwaf/libraries/

# lsqlite3 / luafilesystem for luajit
RUN set -x \
    && echo '== building lsqlite3 / lfs ==' \
    && gcc -O2 -fPIC -shared -DluaL_reg=luaL_Reg -o /usr/local/openresty/luajit/lib/lua/5.1/lsqlite3.so \
         /usr/local/build-vendor/lsqlite3/lsqlite3.c -I/usr/local/openresty/luajit/include/luajit-2.1 -lsqlite3 \
    && gcc -O2 -fPIC -shared -o /usr/local/openresty/luajit/lib/lua/5.1/lfs.so \
         /usr/local/build-vendor/luafilesystem/src/lfs.c -I/usr/local/openresty/luajit/include/luajit-2.1

RUN set -x \
    && rm -rf /tmp/libmaxminddb /tmp/mmdb-install /tmp/vendor /usr/local/build-vendor /tmp/openresty-${RESTY_VERSION} \
    && ldconfig \
    && mkdir -p /var/run/openresty \
    && ln -sf /dev/stdout /usr/local/openresty/nginx/logs/access.log \
    && ln -sf /dev/stderr /usr/local/openresty/nginx/logs/error.log

# OpenResty's LuaJIT2 fork ships extra table built-ins registered via
# lj_lib_prereg (table.nkeys/.clone/.isarray/.isempty); the loongarch64
# LuaJIT port (loongson/LuaJIT, based on stock LuaJIT 2.1) only provides
# table.new/.clear, so 1pwaf's ipmatcher and lua-resty-openssl fail on
# require("table.nkeys"). Shim it with a pure-Lua equivalent at the exact
# path the module resolver probes.
RUN set -x \
    && mkdir -p /usr/local/openresty/lualib/table \
    && printf 'return function(t)\n  local n = 0\n  for _ in pairs(t) do n = n + 1 end\n  return n\nend\n' \
       > /usr/local/openresty/lualib/table/nkeys.lua \
    && /usr/local/openresty/bin/resty -e 'assert(require("table.nkeys")({1,2,3,a="x"}) == 4); print("table.nkeys shim OK")'

ENV PATH=$PATH:/usr/local/openresty/luajit/bin:/usr/local/openresty/nginx/sbin:/usr/local/openresty/bin

ENV LUA_PATH="/usr/local/openresty/site/lualib/?.ljbc;/usr/local/openresty/site/lualib/?/init.ljbc;/usr/local/openresty/lualib/?.ljbc;/usr/local/openresty/lualib/?/init.ljbc;/usr/local/openresty/site/lualib/?.lua;/usr/local/openresty/site/lualib/?/init.lua;/usr/local/openresty/lualib/?.lua;/usr/local/openresty/lualib/?/init.lua;./?.lua;/usr/local/openresty/luajit/share/luajit-2.1/?.lua;/usr/local/share/lua/5.1/?.lua;/usr/local/share/lua/5.1/?/init.lua;/usr/local/openresty/luajit/share/lua/5.1/?.lua;/usr/local/openresty/luajit/share/lua/5.1/?/init.lua"

ENV LUA_CPATH="/usr/local/openresty/site/lualib/?.so;/usr/local/openresty/lualib/?.so;./?.so;/usr/local/lib/lua/5.1/?.so;/usr/local/openresty/luajit/lib/lua/5.1/?.so;/usr/local/lib/lua/5.1/loadall.so;/usr/local/openresty/luajit/lib/lua/5.1/?.so"

COPY nginx.conf /usr/local/openresty/nginx/conf/nginx.conf
COPY nginx.vh.default.conf /etc/nginx/conf.d/default.conf

RUN bash /tmp/default.sh

CMD ["/usr/local/openresty/bin/openresty", "-g", "daemon off;"]

STOPSIGNAL SIGQUIT
