<?php

namespace Dockerpilot\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppLoginCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:login')
            ->setDescription('Login to application container.')
            ->setHelp('Login to application container.')
            ->addOption('app', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->userInput($input, $output)) {
            if ($this->login($output)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ask user for the application.
     *
     * @param $input
     * @param $output
     * @return array
     */
    protected function userInput($input, $output)
    {
        return $this->askForApp($input, $output, 'In which app would you like to login?', 'started');
    }

    /**
     * Login to application container.
     *
     * @param $output
     * @return bool
     */
    protected function login($output)
    {
        $container = 'dp-app-' . $this->app;
        $command = "docker exec --user dockerpilot -it $container /bin/bash";

        if (!sp_is_windows()) {
            $process = new Process($command);
            try {
                $process->setTty(true);
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>" . $e->getMessage() . "</error>");
            }
        } else {
            $output->writeln("<info>Dockerpilot can't login to application containers on Windows.</info>");
            $output->writeln("<info>Copy and paste the following command to login:</info>\n");
            $output->writeln($command);
        }

        return true;
    }
}
