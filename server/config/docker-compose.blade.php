version: '3'
services:

  nginx:
    image: sitepilot/nginx
    labels:
        com.github.jrcs.letsencrypt_nginx_proxy_companion.nginx_proxy: "true"
    container_name: sp-nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - serverpilot-data:/etc/nginx/conf.d
      - ./data/vhost.d:/etc/nginx/vhost.d
      - ./data/html:/usr/share/nginx/html
      - ./data/certs:/etc/nginx/certs:ro
      - ./data/logs/nginx:/var/log/nginx:cached
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./fastcgi.conf:/etc/nginx/fastcgi.conf
      - ../apps:/apps

  nginx-gen:
    image: jwilder/docker-gen
    command: -notify-sighup sp-nginx -watch -wait 5s:30s /etc/docker-gen/templates/nginx.tmpl /etc/nginx/conf.d/default.conf
    container_name: sp-nginx-gen
    restart: always
    volumes:
      - serverpilot-data:/etc/nginx/conf.d
      - ./data/vhost.d:/etc/nginx/vhost.d
      - ./data/html:/usr/share/nginx/html
      - ./data/certs:/etc/nginx/certs:ro
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - ./nginx.tmpl:/etc/docker-gen/templates/nginx.tmpl:ro

  nginx-letsencrypt:
    image: jrcs/letsencrypt-nginx-proxy-companion
    container_name: sp-letsencrypt
    restart: always
    volumes:
      - serverpilot-data:/etc/nginx/conf.d
      - ./data/vhost.d:/etc/nginx/vhost.d
      - ./data/html:/usr/share/nginx/html
      - ./data/certs:/etc/nginx/certs:rw
      - /var/run/docker.sock:/var/run/docker.sock:ro
    environment:
      NGINX_DOCKER_GEN_CONTAINER: "sp-nginx-gen"
      NGINX_PROXY_CONTAINER: "sp-nginx"

  database:
    image: sitepilot/mysql:5.7
    container_name: sp-db
    volumes:
      - ./data/mysql:/var/lib/mysql:cached
      - {{ SERVER_BACKUP_DIR }}:/serverpilot/backup
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: {{ MYSQL_ROOT_PASSWORD }}
      MYSQL_DATABASE: serverpilot

volumes:
    serverpilot-data:

networks:
  default:
    external:
      name: serverpilot
