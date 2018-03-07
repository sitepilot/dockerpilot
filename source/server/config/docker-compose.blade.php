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
      - ../../data/vhost.d:/etc/nginx/vhost.d
      - ../../data/html:/usr/share/nginx/html
      - ../../data/certs:/etc/nginx/certs:ro
      - ../../data/logs/nginx:/var/log/nginx:cached
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./proxy.conf:/etc/nginx/conf.d/proxy.conf
      - ../../apps:/apps

  nginx-gen:
    image: jwilder/docker-gen
    command: -notify-sighup dp-nginx -watch -wait 5s:30s /etc/docker-gen/templates/nginx.tmpl /etc/nginx/conf.d/default.conf
    container_name: dp-nginx-gen
    restart: always
    volumes:
      - dockerpilot-data:/etc/nginx/conf.d
      - ../../data/vhost.d:/etc/nginx/vhost.d
      - ../../data/html:/usr/share/nginx/html
      - ../../data/certs:/etc/nginx/certs:ro
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - ./nginx.tmpl:/etc/docker-gen/templates/nginx.tmpl:ro

  nginx-letsencrypt:
    image: jrcs/letsencrypt-nginx-proxy-companion
    container_name: dp-letsencrypt
    restart: always
    volumes:
      - dockerpilot-data:/etc/nginx/conf.d
      - ../../data/vhost.d:/etc/nginx/vhost.d
      - ../../data/html:/usr/share/nginx/html
      - ../../data/certs:/etc/nginx/certs:rw
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
      - ../../data/mysql:/var/lib/mysql:cached
      - {{ SERVER_BACKUP_DIR }}:/dockerpilot/backups
      - ../../apps:/dockerpilot/apps
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
