version: '2.2'

services:

  app:
    image: sitepilot/php:7.1
    container_name: sp-app-{{$env['APP_NAME']}}
    restart: always
    environment:
      - VIRTUAL_HOST={{ $env['APP_DOMAINS'] }}
      - VIRTUAL_ROOT=/var/www/html{{ ! empty($env['APP_PUBLIC']) ? "/" . $env['APP_PUBLIC'] : "" }}
      - VIRTUAL_PORT=9000
      - VIRTUAL_PROTO=fastcgi
      - VIRTUAL_PUBLIC=/apps/{{$env['APP_NAME']}}/app{{ ! empty($env['APP_PUBLIC']) ? "/" . $env['APP_PUBLIC'] : "" }}
      {{ ! empty($env['APP_CACHE']) && $env['APP_CACHE'] == 'on' || empty($env['APP_CACHE']) ? "- VIRTUAL_CACHE=true" : "" }}
      {{ ! empty($env['APP_SSL_DOMAINS']) ? "- LETSENCRYPT_HOST=" . $env['APP_SSL_DOMAINS'] : "" }}
      {{ ! empty($env['APP_SSL_EMAIL']) ? "- LETSENCRYPT_EMAIL=".$env['APP_SSL_EMAIL'] : "" }}
    volumes:
      - ./app:/var/www/html
      - ./php.ini:/usr/local/etc/php/php.ini
    cpus: {{ ! empty($env['APP_CPUS']) ? $env['APP_CPUS'] : "1" }}
    mem_limit: {{ ! empty($env['APP_MEMORY']) ? $env['APP_MEMORY'] * 1000000 : 512 * 1000000 }}

networks:
  default:
    external:
      name: serverpilot
