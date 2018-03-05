<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
            ->setDescription('Stops Mailcatcher.')
            ->setHelp('This command stops Mailcatcher.');
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
            $this->stopMailcatcher($output);
            $output->writeln("<info>Mailcatcher stopped!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop Mailcatcher: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Stop server.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function stopMailcatcher(OutputInterface $output)
    {
        $output->writeln("Stopping mailcatcher, please wait...");
        $process1 = new Process('cd ' . SERVER_WORKDIR . '/tools/mailcatcher && docker-compose down');
        $process2 = new Process('cd ' . SERVER_WORKDIR . '/tools/mailcatcher && docker-compose rm');

        try {
            $process1->mustRun();
            $process2->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
