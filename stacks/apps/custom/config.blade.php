name: {{ $app['name'] }}
stack: apps/custom
host: {{ $app['host'] }}
image: jwilder/whoami:latest

volumes:
  data: '/var/www/html'
  logs: '/var/logs'

database:
  host: {{ $app['database']['host'] }}
  user: {{ $app['database']['user'] }}
  password: {{ $app['database']['password'] }}
  name: {{ $app['database']['name'] }}

network:
  domains: {{ $app['name'] }}.{{ $server['appDomain'] }}
  port: 8000

monitor:
  domain: http://{{ $app['name'] }}.{{ $server['appDomain'] }}