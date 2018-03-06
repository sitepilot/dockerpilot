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
      {{ ! empty($env['APP_SSL_EMAIL']) ? "LETSENCRYPT_EMAIL: ".$env['APP_SSL_EMAIL'] : "" }}
    volumes:
      - {{ SERVER_PATH }}/apps/{{ $env['APP_NAME'] }}/ssmtp.conf:/etc/ssmtp/ssmtp.conf
      - {{ SERVER_PATH }}/apps/{{ $env['APP_NAME'] }}/app:{{$env['APP_MOUNT_POINT']}}:cached
      - {{ SERVER_PATH }}/apps/{{ $env['APP_NAME'] }}/php.ini:/usr/local/etc/php/php.ini
    cpus: {{ ! empty($env['APP_CPUS']) ? $env['APP_CPUS'] : "1" }}
    mem_limit: {{ ! empty($env['APP_MEMORY']) ? $env['APP_MEMORY'] * 1000000 : 512 * 1000000 }}

networks:
  default:
    external:
      name: dockerpilot
