<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppStopCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:stop')
            ->setDescription('Stop an application.')
            ->setHelp('This command stops an app.')
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
            $this->stopApp($output);
            $output->writeln('<info>App stopped!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to stop application: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to stop?', 'running');
    }

    /**
     * Stops the app.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function stopApp(OutputInterface $output)
    {
        $output->writeln("Stopping app " . $this->app . ", please wait...");
        if (file_exists($this->appDir . '/interface.php')) {
            require_once $this->appDir . '/interface.php';
            $appInterfaceClass = '\Dockerpilot\App\\' . ucfirst($this->app) . '\AppInterface';
            if (method_exists($appInterfaceClass, 'stop')) {
                $appInterfaceClass::stop($output);
            }
        }

        $process = new Process('cd ' . $this->appDir . ' && docker-compose down');
        try {
            $process->mustRun();
            $process = new Process('cd ' . $this->appDir . ' && docker-compose rm');
            try {
                $process->mustRun();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
