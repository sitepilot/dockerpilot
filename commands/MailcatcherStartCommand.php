<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MailcatcherStartCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mailcatcher:start')
             ->setDescription('Starts mailcatcher.')
             ->setHelp('This command starts mailcatcher.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->startMailcatcher($output)) {
            $output->writeln("<info>Mailcatcher started!</info>");
        }
    }

    /**
     * Starts mailcatcher.
     *
     * @return bool
     */
    protected function startMailcatcher($output)
    {
        $output->writeln("Starting mailcatcher, please wait...");
        $process = new Process('cd tools/mailcatcher && docker-compose up -d');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
