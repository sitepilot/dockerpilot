<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Philo\Blade\Blade;

class ServerCronCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('server:cron')
             ->setDescription('Run cron jobs for the server.')
             ->setHelp('This command runs cron jobs for the server.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->backupApps($output)) {
            if($this->updateApps($output)) {
                $output->writeln("<info>Cron done!</info>");
            }
        }
    }

    /**
     * Backup each app.
     *
     * @return bool
     */
    protected function backupApps($output)
    {
        // Get apps
        $apps = sp_get_apps();

        // Backup each app
        foreach($apps as $dir=>$app) {
          $output->writeln("[CRON] Backup $app...");
          $arguments = array(
              '--appName'  => $app
          );
          $input = new ArrayInput($arguments);

          $command = $this->getApplication()->find('app:backup');
          $command->run($input, $output);
        }

        return true;
    }

    /**
     * Update each app.
     *
     * @return bool
     */
    protected function updateApps($output)
    {
        // Get apps
        $apps = sp_get_apps();

        // Update each app
        foreach($apps as $dir=>$app) {
          $env = sp_get_env($dir);
          if(! empty($env['APP_TEMPLATE']) && $env['APP_TEMPLATE'] == 'wordpress')
          {
            $output->writeln("[CRON] Updating $app...");
            $arguments = array(
                '--appName'  => $app
            );
            $input = new ArrayInput($arguments);

            $command = $this->getApplication()->find('wp:update');
            $command->run($input, $output);
          }
        }

        return true;
    }
}
