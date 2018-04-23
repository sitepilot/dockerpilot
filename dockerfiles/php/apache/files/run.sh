#!/bin/sh
if [[ -z $APP_NAME ]]; then
    echo "Can't initialize the application, missing environment variables."
    exit 1
fi

addgroup -g 1000 -S sitepilot
adduser -u 1000 -D -S -G sitepilot sitepilot

if [ ! -d /var/www/html ] ; then
  mkdir -p /var/www/html
fi

# update configuration
export APP_PHP_DOMAIN=$APP_NAME.getsitepilot.com

sed -i "s/{APP_DOMAIN}/$APP_PHP_DOMAIN/g" /etc/apache2/httpd.conf
sed -i "s/;sendmail_path =/sendmail_path = \/usr\/bin\/msmtp --logfile \/var\/www\/logs\/mail.log -a sitepilot -t/g" /etc/php7/php.ini

# create folders
mkdir -p /var/www/logs/php-fpm
mkdir -p /var/www/logs/apache
mkdir -p /tmp/apache

# set permissions
chown sitepilot:sitepilot /tmp/apache
chown -R sitepilot:sitepilot /var/www

# mail log
touch /var/www/logs/mail.log
chown -R sitepilot:sitepilot /var/www/logs/mail.log

/usr/bin/supervisord -c /etc/supervisord.conf
