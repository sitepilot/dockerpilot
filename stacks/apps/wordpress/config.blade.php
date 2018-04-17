name: {{ $app['name'] }}
stack: apps/wordpress
host: {{ $app['host'] }}

network:
  domains: {{ $app['name'] }}.{{ $server['appDomain'] }}

database:
  host: {{ $app['database']['host'] }}
  user: {{ $app['name'] }}
  password: {{ $app['database']['password'] }}

admin:
  user: {{ $app['admin']['user'] }}
  email: {{ $app['admin']['email'] }}

monitor:
  domain: http://{{ $app['name'] }}.{{ $server['appDomain'] }}