version: '2.2'

services:

  app:
    build:
      context: ../../source/dockerfiles/php/7.1/
      args:
        - USER={{ SERVER_USER }}
    container_name: dp-app-{{$env['APP_NAME']}}
    expose:
      - 80
    restart: always
    environment:
      {{ "VIRTUAL_HOST: " . $env['APP_DOMAINS'] }}
      {{ ! empty($env['APP_SSL_DOMAINS']) ? "LETSENCRYPT_HOST: " . $env['APP_SSL_DOMAINS'] : "" }}
      {{ ! empty($env['APP_SSL_EMAIL']) ? "LETSENCRYPT_EMAIL: " . $env['APP_SSL_EMAIL'] : "" }}
    volumes:
      - ./ssmtp.conf:/etc/ssmtp/ssmtp.conf
      {{ ! SERVER_DOCKER_SYNC ? "- ./app:" . $env['APP_MOUNT_POINT'] . ":cached" : "- dp-app-" . $env['APP_NAME'] . "-sync:" . $env['APP_MOUNT_POINT'] }}
      - ./php.ini:/usr/local/etc/php/php.ini
      {{ ! empty($env['APP_VOLUME_1']) ? "- " . $env['APP_VOLUME_1'] : "" }}
    {{ ! empty($env['APP_CPUS']) ? "cpus: " . $env['APP_CPUS'] : "" }}
    {{ ! empty($env['APP_MEMORY']) ? "mem_limit: " . ($env['APP_MEMORY'] * 1000000 / 512 * 1000000) : "" }}

@if(SERVER_DOCKER_SYNC)
volumes:
  dp-app-{{$env['APP_NAME']}}-sync:
    external: true
@endif

networks:
  default:
    external:
      name: dockerpilot
