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
      {{ ! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off' ? "VIRTUAL_HOST: " . $env['APP_DOMAINS'] : "" }}
      {{ (! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off') && ! empty($env['APP_SSL_DOMAINS']) ? "LETSENCRYPT_HOST: " . $env['APP_SSL_DOMAINS'] : "" }}
      {{ (! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off') && ! empty($env['APP_SSL_EMAIL']) ? "LETSENCRYPT_EMAIL: ".$env['APP_SSL_EMAIL'] : "" }}
    volumes:
      - ./ssmtp.conf:/etc/ssmtp/ssmtp.conf
      - ./app:{{$env['APP_MOUNT_POINT']}}:cached
      - ./php.ini:/usr/local/etc/php/php.ini
      {{ ! empty($env['APP_VOLUME_1']) ? "- " . $env['APP_VOLUME_1'] : "" }}
    cpus: {{ ! empty($env['APP_CPUS']) ? $env['APP_CPUS'] : "1" }}
    mem_limit: {{ ! empty($env['APP_MEMORY']) ? $env['APP_MEMORY'] * 1000000 : 512 * 1000000 }}

networks:
  default:
    external:
      name: dockerpilot
