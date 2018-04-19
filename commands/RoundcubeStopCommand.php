<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RoundcubeStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('roundcube:stop')
            ->setDescription('Stops Roundcube webmail.')
            ->setHelp('This command stops Roundcube webmail.');
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
            $this->stopRoundcube($output);
            $output->writeln("<info>Roundcube stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop Roundcube: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stop Roundcube.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopRoundcube(OutputInterface $output)
    {
        $output->writeln("Stopping Roundcube, please wait...");
        $process1 = new Process('docker stack rm roundcube');

        try {
            $process1->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
