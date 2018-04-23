FROM alpine:3.6
MAINTAINER Sitepilot <support@sitepilot.io>

RUN echo 'http://dl-4.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories\
    && apk update \
    && apk add --no-cache \
    bash \
    vim \
    apache2-proxy \
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
    # php7-opcache \
    php7-soap \
    php7-ctype \
    # php7-apcu \
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

RUN mkdir /run/apache2
ADD files/httpd.conf /etc/apache2/httpd.conf
ADD files/php-fpm.conf /etc/php7/php-fpm.conf
ADD files/supervisord.conf /etc/supervisord.conf
ADD files/msmtp.conf /etc/msmtprc

ADD files/run.sh /
RUN chmod +x /run.sh

COPY files/dp-backup.sh /usr/bin/dp-backup
COPY files/dp-restore.sh /usr/bin/dp-restore
RUN chmod +x /usr/bin/dp-backup
RUN chmod +x /usr/bin/dp-restore

COPY files/ioncube/00-ioncube.ini /etc/php7/conf.d/00-ioncube.ini
COPY files/ioncube/ioncube_loader_lin_7.1.so /usr/lib/php7/modules/ioncube_loader_lin_7.1.so
COPY files/ioncube/ioncube_loader_lin_7.1_ts.so /usr/lib/php7/modules/ioncube_loader_lin_7.1_ts.so

EXPOSE 80
VOLUME ["/var/www/html"]
WORKDIR "/var/www/html"

CMD ["/run.sh"]