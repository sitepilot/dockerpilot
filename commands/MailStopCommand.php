<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MailStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mail:stop')
            ->setDescription('Stops the mail relay server.')
            ->setHelp('This command stops the mail relay server.');
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
            $output->writeln("<info>Mail relay server stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop mail relay server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stop the mail relay server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopServer(OutputInterface $output)
    {
        $output->writeln("Stopping mail relay server, please wait...");
        $process1 = new Process('docker stack rm mail');

        try {
            $process1->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
