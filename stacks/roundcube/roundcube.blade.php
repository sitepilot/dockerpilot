version: '3.5'
services:

  webmail:
    image: sitepilot/roundcube:latest
    networks:
      - dockerpilot
    volumes:
      - {{ $roundcube['storagePath'] }}:/var/www/db/
    deploy:
      placement:
        constraints: [node.role == manager]
      labels:
        - com.df.notify=true
        - com.df.serviceDomain={{ $roundcube['domain'] }}
        - com.df.port=80
        - com.df.httpsOnly={{ $roundcube['httpsOnly'] }}

networks:
  dockerpilot:
    external: true