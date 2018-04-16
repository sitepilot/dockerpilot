version: '3.5'
services:

   wp:
     image: sitepilot/wordpress:latest
     networks:
       - dockerpilot
     environment:
       APP_NAME: {{ $app['name'] }}
       APP_DB_PASS: {{ $app['database']['password'] }}
       APP_DB_HOST: {{ $app['database']['host'] }}
       APP_ADMIN_USER: {{ $app['admin']['user'] }}
       APP_ADMIN_EMAIL: {{ $app['admin']['email'] }}
     volumes:
       - {{ $apps['storagePath'] }}:/var/www/html
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