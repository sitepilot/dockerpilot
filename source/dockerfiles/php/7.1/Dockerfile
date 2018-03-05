FROM php:7.1-apache
MAINTAINER Dockerpilot <support@sitepilot.io>
ARG USER

COPY php.ini /usr/local/etc/php/

# Set permissions
RUN groupadd $USER \
    && useradd -g $USER $USER

# Install packages
RUN apt-get update \
    && apt-get install nano zlib1g-dev git ssmtp mailutils mysql-client libpng-dev -y

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install -j$(nproc) mysqli opcache zip pdo pdo_mysql gd

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && /usr/local/bin/composer --version

ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Install WPCLI
RUN cd ~/ && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && cd ~/ && chmod +x wp-cli.phar \
    && cd ~/ && mv wp-cli.phar /usr/local/bin/wp

# Run apache as dockerpilot user
RUN echo export APACHE_RUN_USER=$USER >> /etc/apache2/envvars
RUN echo export APACHE_RUN_GROUP=$USER >> /etc/apache2/envvars

# Enable Apache rewrite module
RUN a2enmod rewrite
RUN service apache2 restart