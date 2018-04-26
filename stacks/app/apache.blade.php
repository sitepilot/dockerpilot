<VirtualHost *:81>
    Define DOCUMENT_ROOT /srv/users/{{ $app['user'] }}/apps/{{ $app['name'] }}/public
    Define PHP_PROXY_URL fcgi://127.0.0.1:9000

    ServerAdmin webmaster@
    DocumentRoot ${DOCUMENT_ROOT}
    # ServerName {{ $app['name'] }}.{{ $server['domain'] }}
    # ServerAlias www.{{ $app['name'] }}.{{ $server['domain'] }} {{ $app['network']['domains'] }}

    ErrorLog "/srv/users/{{ $app['user'] }}/log/{{ $app['name'] }}/{{ $app['name'] }}_apache.error.log"
    CustomLog "/srv/users/{{ $app['user'] }}/log/{{ $app['name'] }}/{{ $app['name'] }}_apache.access.log" common

    RemoteIPHeader X-Real-IP
    SetEnvIf X-Forwarded-SSL on HTTPS=on
    SetEnvIf X-Forwarded-Proto https HTTPS=on

    #SuexecUserGroup sitepilot sitepilot
    AcceptPathInfo on

    DirectoryIndex index.html index.htm index.php

    <Directory ${DOCUMENT_ROOT}>
        AllowOverride All
        Require all granted

        RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_URI} !-f
        RewriteRule \.php$ - [R=404]
    </Directory>

    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .+
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    <Files *.php>
        SetHandler proxy:${PHP_PROXY_URL}
    </Files>

    <Proxy ${PHP_PROXY_URL}>
        ProxySet timeout=3600 retry=0
    </Proxy>
</VirtualHost>