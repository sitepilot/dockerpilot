<?php

namespace Dockerpilot\Command;

use Exception;
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
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->userInput($input, $output);
            $this->login($output);
        } catch (Exception $e) {
            $output->writeln("<error>Failed to login: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param $input
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function userInput($input, $output)
    {
        $this->askForApp($input, $output, 'In which app would you like to login?', 'started');
    }

    /**
     * Login to application container.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function login(OutputInterface $output)
    {
        $container = 'dp-app-' . $this->app;
        $command = "docker exec --user " . SERVER_USER . " -it $container /bin/bash";

        if (!dp_is_windows()) {
            $process = new Process($command);
            try {
                $process->setTty(true);
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            $output->writeln("<info>Dockerpilot can't login to application containers on Windows.</info>");
            $output->writeln("<info>Copy and paste the following command to login:</info>\n");
            $output->writeln($command);
        }
    }
}
