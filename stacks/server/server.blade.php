version: '3.5'
services:

  proxy:
    image: {{ $server['proxyImage'] }}
    ports:
      - target: 80
        published: 80
        mode: host
      - target: 443
        published: 443
        mode: host
    networks:
      - dockerpilot
    environment:
      - LISTENER_ADDRESS=listener
      - MODE=swarm
    deploy:
      mode: global

  listener:
    image: vfarcic/docker-flow-swarm-listener:latest
    networks:
      - dockerpilot
    depends_on:
      - proxy
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
    environment:
      - DF_NOTIFY_CREATE_SERVICE_URL=http://proxy:8080/v1/docker-flow-proxy/reconfigure
      - DF_NOTIFY_REMOVE_SERVICE_URL=http://proxy:8080/v1/docker-flow-proxy/remove
    deploy:
      placement:
        constraints: [node.role == manager]

networks:
  dockerpilot:
    external: true
