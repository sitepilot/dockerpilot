FROM alpine:3.6
MAINTAINER Sitepilot <support@sitepilot.io>

RUN echo 'http://dl-4.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories\
    && apk update \
    && apk add --no-cache \
    bash \
    vim \
    nginx \
    ca-certificates \
    php7-fpm \
    php7-json \
    php7-zlib \
    php7-xml \
    php7-pdo \
    php7-phar \
    php7-openssl \
    php7-pdo_mysql \
    php7-mysqli \
    php7-session \
    php7-gd \
    php7-iconv \
    php7-mcrypt \
    php7-curl \
    php7-opcache \
    php7-ctype \
    php7-apcu \
    php7-intl \
    php7-bcmath \
    php7-mbstring \
    php7-dom \
    php7-xmlreader \
    php7-simplexml \
    php7-zip \
    php7-bz2 \
    mysql-client \
    git \
    curl \
    supervisor \
    msmtp \
    && apk --update --no-cache add tar
RUN rm -rf /var/cache/apk/*

RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php7/php.ini

ADD files/nginx.conf /etc/nginx/
ADD files/php-fpm.conf /etc/php7/
ADD files/supervisord.conf /etc/supervisord.conf
ADD files/msmtp.conf /etc/msmtprc

ADD files/run.sh /
RUN chmod +x /run.sh

COPY files/dp-backup.sh /usr/bin/dp-backup
COPY files/dp-restore.sh /usr/bin/dp-restore
RUN chmod +x /usr/bin/dp-backup
RUN chmod +x /usr/bin/dp-restore

EXPOSE 80
VOLUME ["/var/www/html"]
WORKDIR "/var/www/html"

CMD ["/run.sh"]