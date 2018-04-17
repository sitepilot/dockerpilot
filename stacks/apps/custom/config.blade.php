name: {{ $app['name'] }}
stack: apps/custom
host: {{ $app['host'] }}
image: jwilder/whoami:latest

volumes:
  data: '/var/www/html'
  logs: '/var/logs'

network:
  domains: {{ $app['name'] }}.{{ $server['appDomain'] }}
  port: 8000