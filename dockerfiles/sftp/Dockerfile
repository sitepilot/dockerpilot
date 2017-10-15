FROM atmoz/sftp:latest
MAINTAINER Sitepilot <support@sitepilot.io>

# Add Serverpilot user and group
RUN groupadd serverpilot \
    && useradd -g serverpilot serverpilot

# Add iptables
RUN apt-get update \
    && apt-get install fail2ban iptables -y

RUN touch /var/log/auth.log
