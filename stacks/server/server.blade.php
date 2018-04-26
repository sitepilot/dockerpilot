version: '3.5'
services:

   proxy:
     image: sitepilot/server:latest
     ports:
       - "80:80"
       - "443:443"
       - "2222:2222"
     restart: always
     networks:
       - dockerpilot
     volumes:
       - {{ $server['storagePath'] }}/users:/srv/users
       - {{ $server['storagePath'] }}/config/nginx:/etc/nginx/vhosts.d
       - {{ $server['storagePath'] }}/config/letsencrypt:/etc/letsencrypt
     deploy:
       mode: global

networks:
  dockerpilot:
    external: true