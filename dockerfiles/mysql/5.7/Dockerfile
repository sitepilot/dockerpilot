FROM mysql:5.7
MAINTAINER Dockerpilot <support@sitepilot.io>

# Set permissions
RUN groupadd dockerpilot \
    && useradd -g dockerpilot dockerpilot

# Copy Dockerpilot config
COPY dockerpilot.cnf etc/mysql/conf.d/
