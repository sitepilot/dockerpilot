FROM ubuntu:16.04
MAINTAINER Sitepilot <support@sitepilot.io>

# Install php 7.1
RUN apt-get update \
    && apt-get install -y software-properties-common \
    && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
    && apt-get update

RUN apt-get install -y \
    nano \
    curl \
    mysql-client \
    apache2 \
    php7.1 \
    php7.1-fpm \
    php7.1-bcmath \
    php7.1-bz2 \
    php7.1-calendar \
    php7.1-ctype \
    php7.1-curl \
    php7.1-dom \
    php7.1-exif \
    php7.1-fileinfo \
    php7.1-ftp \
    php7.1-gettext \
    php7.1-gd \
    php7.1-gmp \
    php7.1-iconv \
    php7.1-imap \
    php7.1-intl \
    php7.1-ldap \
    php7.1-json \
    php7.1-mbstring \
    php7.1-mcrypt \
    php7.1-mysql \
    php7.1-mysqli \
    php7.1-mysqlnd \
    php7.1-opcache \
    php7.1-odbc \
    php7.1-pgsql \
    php7.1-phar \
    php7.1-posix \
    php7.1-readline \
    php7.1-shmop \
    php7.1-simplexml \
    php7.1-sockets \
    php7.1-sqlite3 \
    php7.1-sysvmsg \
    php7.1-sysvsem \
    php7.1-sysvshm \
    php7.1-tidy \
    php7.1-tokenizer \
    php7.1-wddx \
    php7.1-xml \
    php7.1-xmlreader \
    php7.1-xmlrpc \
    php7.1-xmlwriter \
    php7.1-xsl \
    php7.1-zip \
    supervisor

RUN rm -rf /etc/apache2
ADD files/apache2 /etc/apache2

RUN rm -rf /etc/php/7.1/fpm/pool.d
ADD files/php/7.1 /etc/php/7.1

ADD files/supervisord.conf /etc/supervisord.conf

ADD files/run.sh /
RUN chmod +x /run.sh

COPY files/bin/dp-reload.sh /usr/bin/dp-reload
RUN chmod +x /usr/bin/dp-reload

COPY files/bin/dp-backup.sh /usr/bin/dp-backup
RUN chmod +x /usr/bin/dp-backup

COPY files/bin/dp-restore.sh /usr/bin/dp-restore
RUN chmod +x /usr/bin/dp-restore

RUN mkdir -p /run/php

EXPOSE 9000

VOLUME ["/srv/users"]
CMD ["/run.sh"]