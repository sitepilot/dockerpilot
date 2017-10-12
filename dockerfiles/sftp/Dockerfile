FROM atmoz/sftp:latest
MAINTAINER Sitepilot <support@sitepilot.io>

# Add Serverpilot user and group
RUN groupadd serverpilot \
    && useradd -g serverpilot serverpilot

# Copy entrypoint file
COPY entrypoint /
