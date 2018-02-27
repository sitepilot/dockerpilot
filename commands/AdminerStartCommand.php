<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
            ->setDescription('Starts adminer.')
            ->setHelp('This command starts adminer.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->startAdminer($output)) {
            $output->writeln("<info>Adminer started!</info>");
        }
    }

    /**
     * Starts adminer.
     *
     * @return bool
     */
    protected function startAdminer($output)
    {
        $output->writeln("Starting adminer, please wait...");
        $process = new Process('cd tools/adminer && docker-compose up -d');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
