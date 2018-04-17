version: '3.5'
services:

   app:
     image: {{ $app['image'] }}
     networks:
       - dockerpilot
@if(!empty($app['environment']) && count($app['environment']) > 0)
     environment:
@foreach($app['environment'] as $item=>$value)
       {{ ! empty($item) ? $item . ": " . $value : "" }}
@endforeach
@endif

@if(! empty($app['volumes']['data']) || ! empty($app['volumes']['logs'] || (! empty($app['volumes']['custom']) && count($app['volumes']['custom']) > 0)))
     volumes:
        {{ ! empty($app['volumes']['data']) ? '- ' . $apps['storagePath'] . '/' .  $app['name'] . '/data:' . $app['volumes']['data'] : ''  }}
        {{ ! empty($app['volumes']['logs']) ? '- ' . $apps['storagePath'] . '/' .  $app['name'] . '/logs:' . $app['volumes']['logs'] : ''  }}
@if(! empty($app['volumes']['custom']))
@foreach($app['volumes']['custom'] as $volume)
        {{ ! empty($volume) ? '- ' . str_replace('{appDir}', $apps['storagePath'] . '/' .  $app['name'], $volume ) : '' }}
@endforeach
@endif
@endif
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