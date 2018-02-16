FROM php:7.1-fpm-alpine
MAINTAINER Sitepilot <support@sitepilot.io>

# Add permissions
RUN set -x \
    && addgroup -g 1000 -S serverpilot \
    && adduser -u 1000 -D -S -G serverpilot serverpilot

# Install deps
RUN set -x \
    && apk add --no-cache nano git zip

# Install php modules
RUN set -x \
    && docker-php-ext-install mysqli opcache pdo pdo_mysql

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && /usr/local/bin/composer --version \
    && export PATH=~/.composer/vendor/bin:$PATH

# Install WPCLI
RUN cd ~/ && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && cd ~/ && chmod +x wp-cli.phar \
    && cd ~/ && mv wp-cli.phar /usr/local/bin/wp

# Install WP Snapshots
RUN composer global config repositories.wpsnapshots git https://github.com/10up/wpsnapshots.git \
    && composer global require 10up/wpsnapshots:dev-master -n