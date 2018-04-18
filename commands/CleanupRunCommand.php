<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CleanupRunCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('cleanup:run')
            ->setDescription('Remove unused data from servers.')
            ->setHelp('This command removes unused data from servers.');
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
            $this->cleanupServer();
            $output->writeln("<info>Cleanup done!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Cleanup failed: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Cleanup unused data from servers.
     *
     * @return void
     * @throws Exception
     */
    protected function cleanupServer()
    {
        $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/cleanupServer.yml');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}