version: '2.2'

services:

  @if(! empty($env['APP_VARNISH']) && $env['APP_VARNISH'] == 'on')

  varnish:
    image: million12/varnish
    container_name: sp-varnish-{{$env['APP_NAME']}}
    depends_on:
      - app
    volumes:
      - ./varnish.vcl:/etc/serverpilot/varnish.vcl
    expose:
      - 80
    restart: always
    environment:
      VIRTUAL_HOST: {{$env['APP_DOMAINS']}}
      LETSENCRYPT_HOST: {{$env['APP_SSL_DOMAINS']}}
      LETSENCRYPT_EMAIL: support@sitepilot.io
      VCL_CONFIG: /etc/serverpilot/varnish.vcl

  @endif

  app:
    image: sitepilot/php-apache:7.1
    container_name: sp-app-{{$env['APP_NAME']}}
    depends_on:
      - db
    expose:
      - 80
    restart: always
    environment:
      {{ ! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off' ? "VIRTUAL_HOST: " . $env['APP_DOMAINS'] : "" }}
      {{ ! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off' ? "LETSENCRYPT_HOST: " . $env['APP_SSL_DOMAINS'] : "" }}
      {{ ! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off' ? "LETSENCRYPT_EMAIL: support@sitepilot.io" : "" }}
      DUMMY_ENV: "serverpilot"
    volumes:
      - ./app:{{$env['APP_MOUNT_POINT']}}
      - ./php.ini:/usr/local/etc/php/php.ini
      {{ ! empty($env['APP_VOLUME_1']) ? "- " . $env['APP_VOLUME_1'] : "" }}

  db:
    image: mysql:5.7
    container_name: sp-db-{{$env['APP_NAME']}}
    volumes:
      - "./data/db:/var/lib/mysql"
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: {{$env['APP_DB_ROOT_PASSWORD']}}
      MYSQL_DATABASE: {{$env['APP_DB_DATABASE']}}
      MYSQL_USER: {{$env['APP_DB_USER']}}
      MYSQL_PASSWORD: {{$env['APP_DB_USER_PASSWORD']}}

networks:
  default:
    external:
      name: serverpilot
