<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppRestartCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:restart')
            ->setDescription('Restart an application.')
            ->setHelp('This command restarts an application.')
            ->addOption('app', null, InputOption::VALUE_OPTIONAL);
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
            $this->userInput($input, $output);
            $this->restartApp($output);
            $output->writeln('<info>App restarted!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to restart the application: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param $input
     * @param $output
     * @throws Exception
     * @return void
     */
    protected function userInput($input, $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to restart?', 'running');
    }

    /**
     * Restart application.
     *
     * @param $output
     * @throws Exception
     * @return void
     */
    protected function restartApp(OutputInterface $output)
    {
        $arguments = array(
            '--app' => $this->app
        );
        $input = new ArrayInput($arguments);

        $command = $this->getApplication()->find('app:stop');
        $command->run($input, $output);

        $command = $this->getApplication()->find('app:start');
        $command->run($input, $output);
    }
}
