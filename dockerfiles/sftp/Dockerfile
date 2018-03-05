FROM atmoz/sftp:latest
MAINTAINER Dockerpilot <support@sitepilot.io>
ARG USER

# Add Dockerpilot user and group
RUN groupadd $USER \
    && useradd -g $USER $USER

# Add iptables
RUN apt-get update \
    && apt-get install fail2ban iptables -y

RUN touch /var/log/auth.log
