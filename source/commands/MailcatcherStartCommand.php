<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
            ->setDescription('Starts Mailcatcher.')
            ->setHelp('This command starts Mailcatcher.');
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
            $this->startMailcatcher($output);
            $output->writeln("<info>Mailcatcher started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start Mailcatcher: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Starts Mailcatcher.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startMailcatcher(OutputInterface $output)
    {
        $output->writeln("Starting Mailcatcher, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/tools/mailcatcher && docker-compose up -d');

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
