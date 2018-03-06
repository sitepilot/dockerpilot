FROM ubuntu:16.04
MAINTAINER Dockerpilot <support@sitepilot.io>
ARG USER

# Set permissions
RUN groupadd $USER \
    && useradd -g $USER $USER

# Install PHP
RUN apt-get update && \
    apt-get dist-upgrade -y && \
    apt-get install -y \
      apache2 \
      php7.0 \
      php7.0-cli \
      libapache2-mod-php7.0 \
      php-apcu \
      php7.0-gd \
      php7.0-json \
      php7.0-ldap \
      php7.0-mbstring \
      php7.0-xml \
      php7.0-xsl \
      php7.0-zip \
      php7.0-soap \
      composer

# Install Docker
RUN apt-get install -y software-properties-common apt-transport-https curl \
    && curl -fsSL https://download.docker.com/linux/ubuntu/gpg | apt-key add - \
    && add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" \
    && apt-get update \
    && apt-get install -y docker-ce \
    && usermod -aG docker $USER

# Install Docker Compose
RUN curl -L https://github.com/docker/compose/releases/download/1.19.0/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose \
    && chmod +x /usr/local/bin/docker-compose \
    && docker-compose --version

COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run

COPY dp /usr/local/bin/dp
RUN chmod +x /usr/local/bin/dp

CMD ["/usr/local/bin/run"]

RUN mkdir -p /dockerpilot
WORKDIR /dockerpilot