FROM php:7.1-apache
MAINTAINER Sitepilot <support@sitepilot.io>

COPY php.ini /usr/local/etc/php/

# Set permissions
RUN groupadd serverpilot \
    && useradd -g serverpilot serverpilot

# Install packages
RUN apt-get update \
    && apt-get install nano zlib1g-dev git -y

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install -j$(nproc) mysqli opcache zip pdo pdo_mysql

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && /usr/local/bin/composer --version

ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Install WPCLI
RUN cd ~/ && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && cd ~/ && chmod +x wp-cli.phar \
    && cd ~/ && mv wp-cli.phar /usr/local/bin/wp

# Install WP Snapshots
RUN composer global config repositories.wpsnapshots git https://github.com/10up/wpsnapshots.git \
    && composer global require 10up/wpsnapshots:dev-master -n

# Run apache as serverpilot user
RUN echo export APACHE_RUN_USER=serverpilot >> /etc/apache2/envvars
RUN echo export APACHE_RUN_GROUP=serverpilot >> /etc/apache2/envvars

# Enable Apache rewrite module
RUN a2enmod rewrite
RUN service apache2 restart
