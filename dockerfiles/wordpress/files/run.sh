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

sed -i "s/{APP_DOMAIN}/$APP_PHP_DOMAIN/g" /etc/nginx/nginx.conf
sed -i "s/;sendmail_path =/sendmail_path = \/usr\/bin\/msmtp --logfile \/var\/www\/logs\/mail.log -a sitepilot -t/g" /etc/php7/php.ini

# create folders
mkdir -p /var/www/logs/php-fpm
mkdir -p /var/www/logs/nginx
mkdir -p /tmp/nginx

# install wordpress
cd /var/www/html
if ! [ -e index.php -a -e wp-includes/version.php ]; then

    if [[ -z $APP_NAME || -z $APP_DB_PASS || -z $APP_DB_NAME || -z $APP_DB_USER || -z $APP_DB_HOST || -z $APP_ADMIN_USER || -z $APP_ADMIN_EMAIL ]]; then
        echo "Can't install WordPress, missing environment variables."
        exit 1
    fi

    echo "WordPress not installed, downloading..."
    wp core download

    echo "Configure WordPress..."
    wp config create --dbhost=$APP_DB_HOST --dbname=$APP_DB_NAME --dbuser=$APP_DB_USER --dbpass=$APP_DB_PASS --dbprefix=sp_

    echo "Installing WordPress..."
    wp core install --url=$APP_DOMAIN --title=$APP_NAME --admin_user=$APP_ADMIN_USER --admin_email=$APP_ADMIN_EMAIL

    echo "Installing Sitepilot plugin..."
    wp plugin install https://update.sitepilot.io/download/sitepilot --activate

    echo "Enable permalinks..."
    wp rewrite structure '/%postname%/'
fi

# set permissions
chown sitepilot:sitepilot /tmp/nginx
chown -R sitepilot:sitepilot /var/www

# mail log
touch /var/www/logs/mail.log
chown -R sitepilot:sitepilot /var/www/logs/mail.log

/usr/bin/supervisord -c /etc/supervisord.conf
