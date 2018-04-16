version: '3.5'
services:

  portainer:
    image: portainer/portainer:latest
    networks:
      - dockerpilot
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - {{ $portainer['storagePath'] }}:/data
    depends_on:
      - proxy
    deploy:
      placement:
        constraints: [node.role == manager]
      labels:
        - com.df.notify=true
        - com.df.serviceDomain={{ $portainer['domain'] }}
        - com.df.port=9000
        - com.df.httpsOnly={{ $portainer['httpsOnly'] }}

networks:
  dockerpilot:
    external: true