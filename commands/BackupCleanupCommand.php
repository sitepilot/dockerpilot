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
        $this->setName('backup:cleanup')
            ->setDescription('Cleanup application backups on every host.')
            ->setHelp('This command cleans up application backups on every host.');
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
            $this->runCleanup($output);
            $output->writeln('<info>Cleanup completed!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to cleanup backups: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Cleanup old backups on every host.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function runCleanup(OutputInterface $output)
    {
        $server = dp_get_config('server');

        $output->writeln("Cleaning up backups, please wait...");
        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/backupCleanup.yml --extra-vars "becomeUser=' . $server['user'] . '"');
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