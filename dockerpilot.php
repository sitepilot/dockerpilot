<?php
// Load composer packages
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

// Set max execution time (5 min)
ini_set('max_execution_time', 300);

// Define workdir
if(! defined('SERVER_WORKDIR')) {
    define( 'SERVER_WORKDIR', __DIR__ );
}

// Include helpers
require_once 'helpers/HelperGeneral.php';

// Get server configuration
$serverConfig = dp_get_config('server');

// Include commands
require_once 'commands/DockerpilotCommand.php';
require_once 'commands/ServerStartCommand.php';
require_once 'commands/ServerStopCommand.php';
require_once 'commands/MysqlStartCommand.php';
require_once 'commands/MysqlStopCommand.php';
require_once 'commands/AdminerStartCommand.php';
require_once 'commands/AdminerStopCommand.php';
require_once 'commands/MailStartCommand.php';
require_once 'commands/MailStopCommand.php';
require_once 'commands/PortainerStartCommand.php';
require_once 'commands/PortainerStopCommand.php';
require_once 'commands/AppCreateCommand.php';

// Create application instance
global $dockerpilot;
$dockerpilot = new Application( $serverConfig['cliName'], 'v1.0.0' );

// Register commands
$dockerpilot->add( new Dockerpilot\Command\ServerStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\ServerStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\MysqlStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\MysqlStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\AdminerStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\AdminerStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\MailStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\MailStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\PortainerStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\PortainerStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppCreateCommand() );

// Run application
$dockerpilot->run();