FROM php:7.1
MAINTAINER Dockerpilot <support@sitepilot.io>
ARG USER

# Set permissions
RUN groupadd $USER \
    && useradd -g $USER $USER

# Install packages
RUN apt-get update \
    && apt-get install nano apt-transport-https ca-certificates curl software-properties-common  -y

# Add Docker GPG key & repo
RUN curl -fsSL https://download.docker.com/linux/ubuntu/gpg | apt-key add -
RUN add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"

# Install docker
RUN apt-get update \
    && apt-get install docker-ce -y

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install -j$(nproc) zip

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && /usr/local/bin/composer --version

ENV PATH="/root/.composer/vendor/bin:${PATH}"