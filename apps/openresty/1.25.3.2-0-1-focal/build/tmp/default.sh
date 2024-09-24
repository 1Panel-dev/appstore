apt-get install -y --no-install-recommends libsqlite3-dev git  python2 libmaxminddb0\
  && ln -s /usr/bin/python2 /usr/bin/python \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && unzip /tmp/libmaxminddb.zip -d /tmp \
  && cd /tmp/libmaxminddb \
  && ./configure \
  && make  \
  && make install \
  && unzip /tmp/libinjection-master.zip -d /tmp \
  && cd /tmp/libinjection-master \
  && make all \
  && unzip /tmp/lsqlite3.zip -d /tmp \
  && cd /tmp/lsqlite3 \
  && make all \
  && mkdir -p /usr/local/openresty/1pwaf/libraries/ \
  && cp /usr/local/lib/libmaxminddb.so.0.0.7 /usr/local/openresty/1pwaf/libraries/libmaxminddb.so \
  && cp /tmp/libinjection-master/src/libinjection.so /usr/local/openresty/1pwaf/libraries/libinjection.so \
  && cp /tmp/lsqlite3/lsqlite3.so /usr/local/openresty/luajit/lib/lua/5.1/lsqlite3.so 
