<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ServerUpdateCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('server:update')
             ->setDescription('Updates the server.')
             ->setHelp('This command updates the server.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->updateServer($output)) {
            $output->writeln("<info>Server updated!</info>");
        }
    }

    /**
     * Update the server.
     *
     * @return bool
     */
    protected function updateServer($output) {
        $output->writeln("Asking git for updates...");
        $process = new Process('cd '.SERVER_WORKDIR.' && git pull origin master');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
