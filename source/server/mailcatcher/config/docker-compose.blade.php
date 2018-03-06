version: '3'
services:

  app:
    image: schickling/mailcatcher
    container_name: dp-mailcatcher
    expose:
      - 80
    restart: always
    ports:
       - "1025:1025"
    environment:
       MAILCATCHER_PORT: 1025
       VIRTUAL_HOST: mailcatcher.local
       VIRTUAL_PORT: 1080

networks:
  default:
    external:
      name: dockerpilot
