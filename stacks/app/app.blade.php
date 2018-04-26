version: '3.5'
services:

  {{ $app['name'] }}:
    image: sitepilot/app:latest
    restart: always
    environment:
      APP_NAME: {{ $app['name'] }}
      APP_USER: {{ $app['user'] }}
      APP_DB_HOST: {{ ! empty($app['database']['host']) ? $app['database']['host'] : 'db' }}
      APP_DB_PASS: {{ ! empty($app['database']['password']) ? $app['database']['password'] : 'secret' }}
      APP_DB_USER: {{ ! empty($app['database']['user']) ? $app['database']['user'] : $app['name'] }}
      APP_DB_NAME: {{ ! empty($app['database']['name']) ? $app['database']['name'] : $app['name'] }}
    volumes:
      - {{ $server['storagePath'] }}/users/{{ $app['user'] }}:/srv/users/{{ $app['user'] }}
    networks:
      - dockerpilot
    deploy:
      placement:
        constraints:
          - node.hostname == {{ $app['host'] }}
      resources:
        limits:
          cpus: '{{ $app['limits']['cpu'] }}'
          memory: {{ $app['limits']['memory'] }}

networks:
  dockerpilot:
    external: true