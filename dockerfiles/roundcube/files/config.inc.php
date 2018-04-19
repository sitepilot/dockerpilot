<?php
$config = array();

$config['db_dsnw'] = 'sqlite:////var/www/db/sqlite.db';

$config['support_url'] = 'https://help.sitepilot.io/';
$config['product_name'] = 'Sitepilot Webmail';

$config['default_host'] = array(
    'ssl://web0118.zxcs.nl:993' => 'mailserver01'
);

$config['smtp_server'] = 'ssl://%h:465';
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';

$config['login_rate_limit'] = 3;