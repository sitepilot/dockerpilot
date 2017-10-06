FROM php:7.1-apache
MAINTAINER Sitepilot <support@sitepilot.io>

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install -j$(nproc) mysqli

# Install packages
RUN apt-get update \
    && apt-get install nano -y

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && /usr/local/bin/composer --version

# Install WPCLI
RUN cd ~/ && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && cd ~/ && chmod +x wp-cli.phar \
    && cd ~/ && mv wp-cli.phar /usr/local/bin/wp \
    && wp --info --allow-root \
