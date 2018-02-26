<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MailcatcherStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mailcatcher:stop')
             ->setDescription('Stops mailcatcher.')
             ->setHelp('This command stops mailcatcher.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        if($this->stopMailcatcher($output)) {
            $output->writeln("<info>Mailcatcher stopped!</info>");
        }
    }

    /**
     * Stop server.
     *
     * @return bool
     */
    protected function stopMailcatcher($output)
    {
        $output->writeln("Stopping mailcatcher, please wait...");
        $process = new Process('cd tools/mailcatcher && docker-compose down');

        try {
            $process->mustRun();

            // Cleanup
            $process = new Process('cd tools/mailcatcher && docker-compose rm');
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
