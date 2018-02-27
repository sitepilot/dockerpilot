<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ServerStopCommand extends Command
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
     * @return void
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        if($this->stopServer($output)) {
            $output->writeln("<info>Server stopped!</info>");
        }
    }

    /**
     * Stop server.
     *
     * @return bool
     */
    protected function stopServer($output)
    {
        $output->writeln("Stopping server, please wait...");
        $process = new Process('cd server && docker-compose down');

        try {
            $process->mustRun();

            // Cleanup
            $process = new Process('cd server && docker-compose rm');
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
