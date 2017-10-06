FROM php:7.1-apache
MAINTAINER Sitepilot <support@sitepilot.io>

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install -j$(nproc) mysqli  \
