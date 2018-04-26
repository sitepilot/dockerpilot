FROM ubuntu:16.04
MAINTAINER Sitepilot <support@sitepilot.io>

RUN apt-get update
RUN apt-get install -y \
    nano \
    nginx \
    supervisor \
    openssh-server \
    varnish \
    letsencrypt

# Copy files
RUN rm -rf /etc/nginx

ADD files/nginx /etc/nginx
ADD files/supervisord.conf /etc/supervisord.conf
ADD files/sshd_config /etc/ssh/sshd_config
ADD files/varnish.vcl /etc/varnish/default.vcl

ADD files/run.sh /
RUN chmod +x /run.sh

COPY files/bin/dp-varnish-config.sh /usr/bin/dp-varnish-config
RUN chmod +x /usr/bin/dp-varnish-config

COPY files/bin/dp-reload.sh /usr/bin/dp-reload
RUN chmod +x /usr/bin/dp-reload

COPY files/bin/dp-letsencrypt.sh /usr/bin/dp-letsencrypt
RUN chmod +x /usr/bin/dp-letsencrypt

RUN mkdir -p /var/run/sshd

EXPOSE 80
EXPOSE 443

VOLUME ["/srv/users"]
CMD ["/run.sh"]