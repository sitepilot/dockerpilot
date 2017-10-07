version: '2.2'

services:

  @if(! empty($env['APP_VARNISH']) && $env['APP_VARNISH'] == 'on')

  varnish:
    image: million12/varnish
    container_name: sp-varish-{{$env['APP_NAME']}}
    depends_on:
      - app
    volumes:
      - ./varnish.vcl:/etc/serverpilot/varnish.vcl
    expose:
      - 80
    environment:
      VIRTUAL_HOST: {{$env['APP_DOMAINS']}}
      VCL_CONFIG: /etc/serverpilot/varnish.vcl

  @endif

  app:
    image: sitepilot/php-apache:7.1
    container_name: sp-app-{{$env['APP_NAME']}}
    depends_on:
      - db
    expose:
      - 80
    environment:
      @if(! isset($env['APP_VARNISH']) || $env['APP_VARNISH'] == 'off')
      VIRTUAL_HOST: {{$env['APP_DOMAINS']}}
      LETSENCRYPT_HOST: {{$env['APP_SSL_DOMAINS']}}
      LETSENCRYPT_EMAIL: support@sitepilot.io
      @endif
      DUMMY_ENV: "serverpilot"
    volumes:
      - ./app:{{$env['APP_MOUNT_POINT']}}
      - ./php.ini:/usr/local/etc/php/php.ini
      @if(! empty($env['APP_VOLUME_1']))- {{$env['APP_VOLUME_1']}} @endif

  db:
    image: mysql:5.7
    container_name: sp-db-{{$env['APP_NAME']}}
    volumes:
      - "./data/db:/var/lib/mysql"
    environment:
      MYSQL_ROOT_PASSWORD: {{$env['APP_DB_ROOT_PASSWORD']}}
      MYSQL_DATABASE: {{$env['APP_DB_DATABASE']}}
      MYSQL_USER: {{$env['APP_DB_USER']}}
      MYSQL_PASSWORD: {{$env['APP_DB_USER_PASSWORD']}}

networks:
  default:
    external:
      name: serverpilot
