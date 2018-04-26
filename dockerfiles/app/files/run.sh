#!/bin/bash
if [ -z $APP_USER ]; then
    exit 1
fi

# Reset user permissions
useradd $APP_USER
chown -R $APP_USER:$APP_USER /srv/users/$APP_USER

# Set apache config
sed -i "s/{APP_USER}/$APP_USER/g" /etc/apache2/apache2.conf
sed -i "s/{APP_USER}/$APP_USER/g" /etc/apache2/vhosts.d/app.conf
sed -i "s/{APP_NAME}/$APP_NAME/g" /etc/apache2/vhosts.d/app.conf

# Set php config
sed -i "s/{APP_USER}/$APP_USER/g" /etc/php/7.1/fpm/pool.d/app.conf
sed -i "s/{APP_NAME}/$APP_NAME/g" /etc/php/7.1/fpm/pool.d/app.conf

/usr/bin/supervisord -c /etc/supervisord.conf