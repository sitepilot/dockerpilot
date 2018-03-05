FROM nginx
MAINTAINER Dockerpilot <support@sitepilot.io>
ARG USER

# Add permissions
RUN groupadd $USER \
    && useradd -g $USER $USER

# Create cache dir
RUN mkdir -p /data/nginx/cache