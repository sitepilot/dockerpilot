<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerRestartCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('server:restart')
            ->setDescription('Restart the server.')
            ->setHelp('This command restarts the server.');
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
            $this->restartServer($output);
            $output->writeln('<info>Server restarted!</info>');
        } catch (Exception  $e) {
            $output->writeln("<error>Failed restart server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Restart the server.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function restartServer(OutputInterface $output)
    {
        $arguments = array();
        $input = new ArrayInput($arguments);

        $command = $this->getApplication()->find('server:stop');
        $command->run($input, $output);

        $command = $this->getApplication()->find('server:start');
        $command->run($input, $output);
    }
}
