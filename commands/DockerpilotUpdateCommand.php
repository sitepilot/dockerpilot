<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerpilotUpdateCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Update Dockerpilot to the latest version.')
            ->setHelp('This command updates Dockerpilot to the latest version.');
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
            $this->updateServer($output);
            $output->writeln("<info>Server updated!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to update the server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Update the server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function updateServer(OutputInterface $output)
    {
        $output->writeln("Asking git for updates...");
        $process1 = new Process('cd ' . SERVER_WORKDIR . ' && git pull origin master');
        $process2 = new Process('cd ' . SERVER_WORKDIR . ' && composer install --no-dev');

        try {
            $process1->mustRun();
            $process2->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
