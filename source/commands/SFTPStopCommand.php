<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SFTPStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('sftp:stop')
            ->setDescription('Stops the SFTP server.')
            ->setHelp('This command stops the SFTP server.');
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
            $this->stopServer($output);
            $output->writeln("<info>SFTP server stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop the SFTP server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stops the SFTP server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopServer(OutputInterface $output)
    {
        $output->writeln("Stopping SFTP server, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/server/sftp && docker-compose down && docker-compose rm');

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
