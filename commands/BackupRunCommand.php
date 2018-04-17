<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupCleanupCommand extends DockerpilotCommand
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
            $this->backupServer($output);
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
    protected function backupServer(OutputInterface $output)
    {
        $server = dp_get_config('server');
        $mysql = dp_get_config('mysql');
        $portainer = dp_get_config('portainer');

        $output->writeln("Backup server data, please wait...");
        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/backupServer.yml --extra-vars "serverUser=' . $server['user'] . ' portainerStoragePath=' . $portainer['storagePath'] . ' mysqlStoragePath=' . $mysql['storagePath'] . '"');
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
}