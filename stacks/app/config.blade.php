name: {{ $app['name'] }}
host: {{ $app['host'] }}
user: {{ $app['user'] }}

database:
  host: {{ $app['database']['host'] }}
  user: {{ $app['database']['user'] }}
  password: {{ $app['database']['password'] }}
  name: {{ $app['database']['name'] }}

monitor:
  domain: http://{{ $app['name'] }}.{{ $server['domain'] }}