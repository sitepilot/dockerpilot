<?php
if (!defined('SERVER_NAME')) {
    define('SERVER_NAME', "Dockerpilot");
}

if (!defined('SERVER_CONSOLE_NAME')) {
    define('SERVER_CONSOLE_NAME', "Dockerpilot");
}

if (!defined('SERVER_USER')) {
    define('SERVER_USER', "dockerpilot");
}

if (!defined('SERVER_APP_DIR')) {
    define('SERVER_APP_DIR', SERVER_WORKDIR . '/apps');
}

if (!defined('SERVER_STACK_DIR')) {
    define('SERVER_STACK_DIR', SERVER_WORKDIR . '/stacks');
}

if (!defined('MYSQL_ROOT_PASSWORD')) {
    define('MYSQL_ROOT_PASSWORD', 'secret');
}

if (!defined('SERVER_BACKUP_DIR')) {
    define('SERVER_BACKUP_DIR', SERVER_WORKDIR . '/storage/backup');
}

if (!defined('SERVER_BACKUP_TIMESTAMP')) {
    define('SERVER_BACKUP_TIMESTAMP', true);
}

if (!defined('SERVER_BACKUP_KEEP_DAYS')) {
    define('SERVER_BACKUP_KEEP_DAYS', 0);
}
