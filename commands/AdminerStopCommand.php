<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
     * @return void
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        if($this->stopAdminer($output)) {
            $output->writeln("<info>Adminer stopped!</info>");
        }
    }

    /**
     * Stop server.
     *
     * @return bool
     */
    protected function stopAdminer($output)
    {
        $output->writeln("Stopping adminer, please wait...");
        $process = new Process('cd tools/adminer && docker-compose down');

        try {
            $process->mustRun();

            // Cleanup
            $process = new Process('cd tools/adminer && docker-compose rm');
            try {
                $process->mustRun();
                return true;
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>".$e->getMessage()."</error>");
            }
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
