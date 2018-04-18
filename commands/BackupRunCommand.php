<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupRunCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('backup:run')
            ->setDescription('Run backups for every app and server data.')
            ->setHelp('This command runs backups for every app and server data.');
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
            $this->server($output);
            $this->apps($output);
            $this->cleanup($output);
            $output->writeln('<info>Backup completed!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Backup failed: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Backup server data.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function server(OutputInterface $output)
    {
        $server = dp_get_config('server');
        $mysql = dp_get_config('mysql');
        $portainer = dp_get_config('portainer');
        $apps = dp_get_config('apps');

        $output->writeln("Backup server data, please wait...");
        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/backupServer.yml --extra-vars "serverUser=' . $server['user'] . ' portainerStoragePath=' . $portainer['storagePath'] . ' mysqlStoragePath=' . $mysql['storagePath'] . ' mysqlBackupPath=' . $mysql['backupPath'] . ' configStoragePath=' . $apps['configPath'] . '"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new Exception('Please enable Ansible in config.yml to use the backup functionality.');
        }
    }

    /**
     * Backup applications.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function apps(OutputInterface $output) {
        $apps = dp_get_apps();
        foreach($apps as $app) {
            $command = $this->getApplication()->find('app:backup');
            $arguments = array(
                '--app' => $app
            );

            $commandInput = new ArrayInput($arguments);
            $command->run($commandInput, $output);
        }
    }

    /**
     * Backup applications.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function cleanup(OutputInterface $output) {
        $command = $this->getApplication()->find('backup:cleanup');
        $commandInput = new ArrayInput([]);
        $command->run($commandInput, $output);
    }
}