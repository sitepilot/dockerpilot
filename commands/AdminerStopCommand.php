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
            ->setDescription('Stops Adminer.')
            ->setHelp('This command stops Adminer.');
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
            $output->writeln("<error>Failed to stop Adminer: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stop Adminer.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopAdminer(OutputInterface $output)
    {
        $output->writeln("Stopping Adminer, please wait...");
        $process1 = new Process('docker stack rm adminer');

        try {
            $process1->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
