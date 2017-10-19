FROM mysql:5.7
MAINTAINER Sitepilot <support@sitepilot.io>

# Set permissions
RUN groupadd serverpilot \
    && useradd -g serverpilot serverpilot

# Copy Serverpilot config
COPY serverpilot.cnf etc/mysql/conf.d/
