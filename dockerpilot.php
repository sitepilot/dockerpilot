<?php
require __DIR__.'/vendor/autoload.php';

// Set max execution time (5 min)
ini_set('max_execution_time', 300);

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
require_once 'commands/DockerpilotCommand.php';
require_once 'commands/ServerStartCommand.php';
require_once 'commands/ServerStopCommand.php';
require_once 'commands/ServerUpdateCommand.php';
require_once 'commands/ServerRestartCommand.php';
require_once 'commands/ServerCronCommand.php';
require_once 'commands/MailcatcherStartCommand.php';
require_once 'commands/MailcatcherStopCommand.php';
require_once 'commands/AppStartCommand.php';
require_once 'commands/AppStopCommand.php';
require_once 'commands/AppDeleteCommand.php';
require_once 'commands/AppCreateCommand.php';
require_once 'commands/AppBackupCommand.php';
require_once 'commands/AppRestartCommand.php';
require_once 'commands/SFTPStartCommand.php';
require_once 'commands/SFTPStopCommand.php';
require_once 'commands/WPInstallCommand.php';
require_once 'commands/WPUpdateCommand.php';

// Create application instance
global $dockerpilot;
$dockerpilot = new Application( SERVER_CONSOLE_NAME, 'v1.0.0' );

// Register commands
$dockerpilot->add( new Dockerpilot\Command\ServerStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\ServerStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\ServerUpdateCommand() );
$dockerpilot->add( new Dockerpilot\Command\ServerRestartCommand() );
$dockerpilot->add( new Dockerpilot\Command\ServerCronCommand() );
$dockerpilot->add( new Dockerpilot\Command\MailcatcherStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\MailcatcherStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppRestartCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppDeleteCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppCreateCommand() );
$dockerpilot->add( new Dockerpilot\Command\AppBackupCommand() );
$dockerpilot->add( new Dockerpilot\Command\SFTPStartCommand() );
$dockerpilot->add( new Dockerpilot\Command\SFTPStopCommand() );
$dockerpilot->add( new Dockerpilot\Command\WPInstallCommand() );
$dockerpilot->add( new Dockerpilot\Command\WPUpdateCommand() );

// Include application command interface
$apps = sp_get_apps();
if(is_array($apps)) {
  foreach($apps as $dir=>$app) {
      $interface = $dir . '/interface.php';
      $appId = sp_get_container_id('dp-app-'.$app);
      if($appId) {
          if(file_exists($interface)) {
              require_once $interface;
          }
      }
  }
}

// Run application
$dockerpilot->run();
