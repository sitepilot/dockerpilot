<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminerStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('adminer:stop')
            ->setDescription('Stops adminer.')
            ->setHelp('This command stops adminer.');
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
            $this->stopAdminer($output);
            $output->writeln("<info>Adminer stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop adminer: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Try to stop Adminer.
     *
     * @throws Exception
     * @param OutputInterface $output
     * @return void
     */
    protected function stopAdminer(OutputInterface $output)
    {
        $output->writeln("Stopping Adminer, please wait...");
        $process1 = new Process('cd ' . SERVER_WORKDIR . '/server/adminer && docker-compose down');
        $process2 = new Process('cd ' . SERVER_WORKDIR . '/server/adminer && docker-compose rm');

        try {
            $process1->mustRun();
            $process2->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
