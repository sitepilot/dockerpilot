version: '3.5'
services:

   wp:
     image: sitepilot/wordpress:latest
     networks:
       - dockerpilot
     environment:
       APP_NAME: {{ $app['name'] }}
       APP_DB_HOST: {{ ! empty($app['database']['host']) ? $app['database']['host'] : 'db' }}
       APP_DB_PASS: {{ ! empty($app['database']['password']) ? $app['database']['password'] : 'secret' }}
       APP_DB_USER: {{ ! empty($app['database']['user']) ? $app['database']['user'] : $app['name'] }}
       APP_DB_NAME: {{ ! empty($app['database']['name']) ? $app['database']['name'] : $app['name'] }}
       APP_DOMAIN: {{ $app['name'] }}.{{ $server['appDomain'] }}
       APP_ADMIN_USER: {{ $app['admin']['user'] }}
       APP_ADMIN_EMAIL: {{ $app['admin']['email'] }}
     volumes:
       - {{ $apps['storagePath'] }}/{{ $app['name'] }}/data:/var/www/html
       - {{ $apps['storagePath'] }}/{{ $app['name'] }}/logs:/var/www/logs
       - {{ $apps['storagePath'] }}/{{ $app['name'] }}/backup:/var/www/backup
     deploy:
       labels:
         - com.df.notify=true
         - com.df.serviceDomain={{ $app['network']['domains'] }}
         - com.df.port={{ $app['network']['port'] }}
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