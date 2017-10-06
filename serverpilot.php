<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

if(! defined('SERVER_WORKDIR')) {
    define( 'SERVER_WORKDIR', __DIR__ );
}

// Include helpers
require_once 'helpers/HelperApp.php';
require_once 'helpers/HelperDocker.php';

// Include config dir
if(file_exists(__DIR__.'/config.php')) {
    require __DIR__.'/config.php';
}
require __DIR__.'/defaults.php';

// Include commands
require_once 'commands/ServerStartCommand.php';
require_once 'commands/ServerStopCommand.php';
require_once 'commands/ServerUpdateCommand.php';
require_once 'commands/MailcatcherStartCommand.php';
require_once 'commands/MailcatcherStopCommand.php';
require_once 'commands/AppStartCommand.php';
require_once 'commands/AppStopCommand.php';
require_once 'commands/AppCreateCommand.php';

// Create application instance
global $serverpilot;
$serverpilot = new Application( SERVER_CONSOLE_NAME, 'v1.0.0' );

// Register commands
$serverpilot->add( new Serverpilot\Command\ServerStartCommand() );
$serverpilot->add( new Serverpilot\Command\ServerStopCommand() );
$serverpilot->add( new Serverpilot\Command\ServerUpdateCommand() );
$serverpilot->add( new Serverpilot\Command\MailcatcherStartCommand() );
$serverpilot->add( new Serverpilot\Command\MailcatcherStopCommand() );
$serverpilot->add( new Serverpilot\Command\AppStartCommand() );
$serverpilot->add( new Serverpilot\Command\AppStopCommand() );
$serverpilot->add( new Serverpilot\Command\AppCreateCommand() );

// Include application command interface
$apps = sp_get_apps();
foreach($apps as $dir=>$app) {
    $interface = $dir . '/interface.php';
    $appId = sp_get_container_id('serverpilot-app-'.$app);
    if($appId) {
        if(file_exists($interface)) {
            require_once $interface;
        }
    }
}

// Run application
$serverpilot->run();
