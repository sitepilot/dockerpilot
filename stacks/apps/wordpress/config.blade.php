name: {{ $app['name'] }}
stack: apps/wordpress
host: {{ $app['host'] }}

network:
  domains: {{ $app['name'] }}.{{ $server['appDomain'] }}

database:
  host: {{ $app['database']['host'] }}
  user: {{ $app['database']['user'] }}
  password: {{ $app['database']['password'] }}
  name: {{ $app['database']['name'] }}

admin:
  user: {{ $app['admin']['user'] }}
  email: {{ $app['admin']['email'] }}

monitor:
  domain: http://{{ $app['name'] }}.{{ $server['appDomain'] }}