[{{ $app['name'] }}]
prefix = /srv/users/{{ $app['user'] }}
chdir = apps/$pool/public

user = {{ $app['user'] }}
group = {{ $app['user'] }}

listen = 9000
listen.owner = {{ $app['user'] }}
listen.group = {{ $app['user'] }}
listen.mode = 660

env[PATH] = /opt/sp/php7.1/bin:/sbin:/usr/sbin:/bin:/usr/bin
env[TMPDIR] = /srv/users/{{ $app['user'] }}/tmp/$pool
env[TEMP] = /srv/users/{{ $app['user'] }}/tmp/$pool
env[TMP] = /srv/users/{{ $app['user'] }}/tmp/$pool

access.log = /srv/users/{{ $app['user'] }}/log/$pool/$pool_php7.1.access.log
access.format = "%{HTTP_X_FORWARDED_FOR}e - [%t] \"%m %r%Q%q\" %s %l - %P %p %{seconds}d %{bytes}M %{user}C%% %{system}C%% \"%{REQUEST_URI}e\""
slowlog = /srv/users/{{ $app['user'] }}/log/$pool/$pool_php7.1.slow.log
request_slowlog_timeout = 5s
catch_workers_output = yes

php_value[error_log] = /srv/users/{{ $app['user'] }}/log/$pool/$pool_php7.1.error.log
php_value[mail.log] = /srv/users/{{ $app['user'] }}/log/$pool/$pool_php7.1.mail.log
php_value[doc_root] = /srv/users/{{ $app['user'] }}/apps/$pool/public
php_value[upload_tmp_dir] = /srv/users/{{ $app['user'] }}/tmp/$pool
php_value[session.save_path] = /srv/users/{{ $app['user'] }}/tmp/$pool

pm.status_path = /php-fpm-status
ping.path = /php-fpm-ping

pm = ondemand
pm.max_children = 20