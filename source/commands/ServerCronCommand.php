<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->backupApps($output);
            $this->updateApps($output);
            $output->writeln("<info>Cron done!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Server cron failed: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Backup each app.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function backupApps(OutputInterface $output)
    {
        // Get apps
        $apps = dp_get_apps();

        // Backup each app
        foreach ($apps as $dir => $app) {
            $output->writeln("[CRON] Backup $app...");
            $arguments = array(
                '--app' => $app
            );
            $input = new ArrayInput($arguments);

            $command = $this->getApplication()->find('app:backup');
            $command->run($input, $output);
        }
    }

    /**
     * Update each app.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function updateApps(OutputInterface $output)
    {
        // Get apps
        $apps = dp_get_apps();

        // Update each app
        foreach ($apps as $dir => $app) {
            $env = dp_get_env($dir);
            if (!empty($env['APP_TEMPLATE']) && $env['APP_TEMPLATE'] == 'wordpress') {
                $output->writeln("[CRON] Updating $app...");
                $arguments = array(
                    '--app' => $app
                );
                $input = new ArrayInput($arguments);

                $command = $this->getApplication()->find('wp:update');
                $command->run($input, $output);
            }
        }
    }
}
