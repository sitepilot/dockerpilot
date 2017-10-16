<?php
if(! defined('SERVER_NAME')) {
    define( 'SERVER_NAME', "Serverpilot" );
}

if(! defined('SERVER_CONSOLE_NAME')) {
    define( 'SERVER_CONSOLE_NAME', "Serverpilot" );
}

if(! defined('SERVER_APP_DIR')) {
    define( 'SERVER_APP_DIR', SERVER_WORKDIR . '/apps' );
}

if(! defined('SERVER_STACK_DIR')) {
    define( 'SERVER_STACK_DIR', SERVER_WORKDIR . '/stacks' );
}

if(! defined('MYSQL_ROOT_PASSWORD')) {
    define( 'MYSQL_ROOT_PASSWORD', 'secret' );
}
