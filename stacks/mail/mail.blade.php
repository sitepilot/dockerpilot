version: '3.5'
services:

  mail:
    image: alterrebe/postfix-relay:latest
    networks:
      - dockerpilot
    environment:
      - RELAY_HOST_NAME={{ $mail['hostName']  }}
      - EXT_RELAY_HOST={{ $mail['relayHost'] }}
      - EXT_RELAY_PORT={{ $mail['relayPort'] }}
      - SMTP_LOGIN={{ $mail['relayLogin'] }}
      - SMTP_PASSWORD={{ $mail['relayPass'] }}
      - USE_TLS={{ $mail['relayTLS'] }}
      - ACCEPTED_NETWORKS=192.168.0.0/16 172.16.0.0/12 10.0.0.0/8
    deploy:
      placement:
        constraints: [node.role == manager]

networks:
  dockerpilot:
    external: true