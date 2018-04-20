<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppBackupCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:backup')
            ->setDescription('Backup an application.')
            ->setHelp('This command backs an application.')
            ->addOption('app', null, InputOption::VALUE_OPTIONAL);
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
            $this->userInput($input, $output);
            $this->backupApp($output);
            $this->notify($output, "Backup done!");
            return 1;
        } catch (Exception $e) {
            $this->notify($output, "Dockerpilot Backup", $e, true, '#e74c3c');
            return 0;
        }
    }

    /**
     * Ask user for the application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to backup?');
    }

    /**
     * Generate application files based on template.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function backupApp(OutputInterface $output)
    {
        // Create app storage folder
        $server = dp_get_config('server');
        $apps = dp_get_config('apps');
        $app = dp_get_app_config($this->appDir);

        $this->notify($output, "Backing up application.");
        $appDataDir = $apps['storagePath'] . '/' . $this->app . '/data';
        $appBackupDir = $apps['storagePath'] . '/' . $this->app . '/backup';
        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/backupApp.yml --extra-vars "becomeUser=' . $server['user'] . ' app=' . $app['name'] . ' host=' . $app['host'] . ' appDataDir=' . $appDataDir . ' appBackupDir=' . $appBackupDir . '"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                $this->notify($output, "Backup result", $process->getOutput());
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new Exception("[" . $app['name'] . "] Please enable Ansible in config.yml to use the backup functionality!");
        }
    }
}