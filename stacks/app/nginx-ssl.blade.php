server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    resolver 127.0.0.11;

    include /etc/nginx/vhosts.d/ssl.d/{{ $app['name'] }}.*.conf;

    root   /srv/users/{{ $app['user'] }}/apps/{{ $app['name'] }}/public;

    access_log  /srv/users/{{ $app['user'] }}/log/{{ $app['name'] }}/{{ $app['name'] }}_nginx.access.log  main;
    error_log  /srv/users/{{ $app['user'] }}/log/{{ $app['name'] }}/{{ $app['name'] }}_nginx.error.log;

    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-SSL on;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Backend {{ $app['name'] }};

@if(! empty($app['varnish']) && $app['varnish'] == 'yes')
    set $upstream http://127.0.0.1:6081;
@else
    set $upstream http://{{ $app['name'] }}:81;
@endif

    location / {
        proxy_pass $upstream;
    }
}