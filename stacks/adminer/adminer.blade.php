version: '3.5'
services:

  app:
    image: adminer:latest
    networks:
      - dockerpilot
    deploy:
      labels:
        - com.df.notify=true
        - com.df.serviceDomain={{ $adminer['domain'] }}
        - com.df.port=8080
      placement:
        constraints: [node.role == manager]

networks:
  dockerpilot:
    external: true