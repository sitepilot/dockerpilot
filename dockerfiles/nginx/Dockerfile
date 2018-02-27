FROM nginx
MAINTAINER Dockerpilot <support@sitepilot.io>

# Add permissions
RUN groupadd dockerpilot \
    && useradd -g dockerpilot dockerpilot

# Create cache dir
RUN mkdir -p /data/nginx/cache