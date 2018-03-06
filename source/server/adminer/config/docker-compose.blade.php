version: '3'
services:

  app:
    build:
      context: ../../dockerfiles/php/7.1/
      args:
        - USER={{ SERVER_USER }}
    container_name: dp-adminer
    expose:
      - 80
    restart: always
    environment:
      VIRTUAL_HOST: adminer.local
    volumes:
      - {{SERVER_PATH}}/source/server/adminer/app:/var/www/html:cached

networks:
  default:
    external:
      name: dockerpilot
