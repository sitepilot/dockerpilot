ARG APP_TAG

FROM wordpress:$APP_TAG
MAINTAINER Sitepilot <support@sitepilot.io>

# Install WP CLI
RUN cd ~/ && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN cd ~/ && chmod +x wp-cli.phar
RUN cd ~/ && mv wp-cli.phar /usr/local/bin/wp
RUN wp --info --allow-root
