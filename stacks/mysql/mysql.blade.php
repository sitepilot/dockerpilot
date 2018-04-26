version: '3.5'
services:

@foreach($mysql['servers'] as $mysqlServer)

   {{ $mysqlServer['name'] }}:
     image: sitepilot/mysql:5.7
     networks:
       - dockerpilot
     secrets:
       - source: db-root-pass-{{ $mysqlServer['name'] }}
         target: db-root-pass
       - source: db-user-pass-{{ $mysqlServer['name'] }}
         target: db-user-pass
       - source: db-user-name-{{ $mysqlServer['name'] }}
         target: db-user-name
     environment:
       - MYSQL_DATABASE={{ $mysqlServer["defaultDatabase"] }}
       - MYSQL_ROOT_PASSWORD_FILE=/run/secrets/db-root-pass
       - MYSQL_USER_FILE=/run/secrets/db-user-name
       - MYSQL_PASSWORD_FILE=/run/secrets/db-user-pass
     volumes:
       - {{ $server['storagePath'] }}/mysql:/var/lib/mysql:cached
       - {{ $server['storagePath'] }}/backup:/backup/mysql
@if($mysqlServer['placement'] == 'manager')
     deploy:
       placement:
         constraints: [node.role == manager]
@else
     deploy:
       placement:
         constraints:
           - node.hostname == {{ $mysqlServer['placement'] }}
@endif

@endforeach

networks:
  dockerpilot:
    external: true

secrets:
@foreach($mysql['servers'] as $mysqlServer)
  db-root-pass-{{ $mysqlServer['name'] }}:
    name: {{ $mysqlServer["rootPassSecret"] }}
    external: true
  db-user-name-{{ $mysqlServer['name'] }}:
    name: {{ $mysqlServer["userNameSecret"] }}
    external: true
  db-user-pass-{{ $mysqlServer['name'] }}:
    name: {{ $mysqlServer["userPassSecret"] }}
    external: true
@endforeach