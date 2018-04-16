<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PortainerStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('portainer:stop')
            ->setDescription('Stops Portainer.')
            ->setHelp('This command stops Portainer.');
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
            $this->stopPortainer($output);
            $output->writeln("<info>Portainer stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop Portainer: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stop Portainer.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopPortainer(OutputInterface $output)
    {
        $output->writeln("Stopping Portainer, please wait...");
        $process1 = new Process('docker stack rm portainer');

        try {
            $process1->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
