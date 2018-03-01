<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminerStartCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('adminer:start')
            ->setDescription('Starts Adminer.')
            ->setHelp('This command starts Adminer.');
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
            $this->startAdminer($output);
            $output->writeln("<info>Adminer started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start Adminer: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Try to start Adminer.
     *
     * @throws Exception
     * @param $output
     * @return void
     */
    protected function startAdminer(OutputInterface $output)
    {
        $output->writeln("Starting Adminer, please wait...");
        $process = new Process('cd tools/adminer && docker-compose up -d');

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
