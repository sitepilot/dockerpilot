version: '3'
services:

  nginx:
    build:
      context: ../dockerfiles/nginx/
      args:
      - USER={{ SERVER_USER }}
    labels:
        com.github.jrcs.letsencrypt_nginx_proxy_companion.nginx_proxy: "true"
    container_name: dp-nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - dockerpilot-data:/etc/nginx/conf.d
      - {{SERVER_PATH}}/data/vhost.d:/etc/nginx/vhost.d
      - {{SERVER_PATH}}/data/html:/usr/share/nginx/html
      - {{SERVER_PATH}}/data/certs:/etc/nginx/certs:ro
      - {{SERVER_PATH}}/data/logs/nginx:/var/log/nginx:cached
      - {{SERVER_PATH}}/source/server/nginx.conf:/etc/nginx/nginx.conf
      - {{SERVER_PATH}}/source/server//proxy.conf:/etc/nginx/conf.d/proxy.conf
      - {{SERVER_PATH}}/apps:/apps

  nginx-gen:
    image: jwilder/docker-gen
    command: -notify-sighup dp-nginx -watch -wait 5s:30s /etc/docker-gen/templates/nginx.tmpl /etc/nginx/conf.d/default.conf
    container_name: dp-nginx-gen
    restart: always
    volumes:
      - dockerpilot-data:/etc/nginx/conf.d
      - {{SERVER_PATH}}/data/vhost.d:/etc/nginx/vhost.d
      - {{SERVER_PATH}}/data/html:/usr/share/nginx/html
      - {{SERVER_PATH}}/data/certs:/etc/nginx/certs:ro
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - {{SERVER_PATH}}/source/server/nginx.tmpl:/etc/docker-gen/templates/nginx.tmpl:ro

  nginx-letsencrypt:
    image: jrcs/letsencrypt-nginx-proxy-companion
    container_name: dp-letsencrypt
    restart: always
    volumes:
      - dockerpilot-data:/etc/nginx/conf.d
      - {{SERVER_PATH}}/data/vhost.d:/etc/nginx/vhost.d
      - {{SERVER_PATH}}/data/html:/usr/share/nginx/html
      - {{SERVER_PATH}}/data/certs:/etc/nginx/certs:rw
      - /var/run/docker.sock:/var/run/docker.sock:ro
    environment:
      NGINX_DOCKER_GEN_CONTAINER: "dp-nginx-gen"
      NGINX_PROXY_CONTAINER: "dp-nginx"

  mysql:
    build:
      context: ../dockerfiles/mysql/5.7/
      args:
        - USER={{ SERVER_USER }}
    container_name: dp-mysql
    ports:
      - "3306:3306"
    volumes:
      - {{SERVER_PATH}}/data/mysql:/var/lib/mysql:cached
      - {{SERVER_PATH}}/data/backups:/dockerpilot/backups
      - {{SERVER_PATH}}/apps:/dockerpilot/apps
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: {{ MYSQL_ROOT_PASSWORD }}
      MYSQL_DATABASE: dockerpilot

volumes:
    dockerpilot-data:

networks:
  default:
    external:
      name: dockerpilot
