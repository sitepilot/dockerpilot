FROM mysql:5.7
MAINTAINER Dockerpilot <support@sitepilot.io>
ARG USER

# Set permissions
RUN groupadd $USER \
    && useradd -g $USER $USER

# Run mysql as dockerpilot user
RUN touch /etc/mysql/conf.d/dockerpilot.cnf
RUN echo "[mysqld]" >> /etc/mysql/conf.d/dockerpilot.cnf
RUN echo "user=$USER" >> /etc/mysql/conf.d/dockerpilot.cnf
