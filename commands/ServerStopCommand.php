<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ServerStopCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('server:stop')
            ->setDescription('Stops the server.')
            ->setHelp('This command stops the server.');
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
            $output->writeln("<info>Server stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop the server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stop server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopServer(OutputInterface $output)
    {
        $output->writeln("Stopping server, please wait...");
        $process1 = new Process('docker stack rm server');

        try {
            $process1->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
