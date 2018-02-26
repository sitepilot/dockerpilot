FROM atmoz/sftp:latest
MAINTAINER Dockerpilot <support@sitepilot.io>

# Add Dockerpilot user and group
RUN groupadd dockerpilot \
    && useradd -g dockerpilot dockerpilot

# Add iptables
RUN apt-get update \
    && apt-get install fail2ban iptables -y

RUN touch /var/log/auth.log
