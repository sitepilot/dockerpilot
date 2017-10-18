<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AppRestartCommand extends ServerpilotCommand
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
             ->addOption('appName', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->userInput($input, $output)) {
            if($this->restartApp($output)){
                $output->writeln('<info>App restarted!</info>');
            }
        }
    }

    /**
     * Ask user for name and app.
     *
     * @return bool
     */
    protected function userInput($input, $output)
    {
        return $this->askForApp($input, $output, 'Which app would you like to restart?', 'running');
    }

    /**
     * Restart application.
     *
     * @return bool
     */
    protected function restartApp($output) {
      $command = $this->getApplication()->find('app:stop');
      $arguments = array(
          '--appName'  => $this->appName
      );
      $stopInput = new ArrayInput($arguments);
      $command->run($stopInput, $output);

      $command = $this->getApplication()->find('app:start');
      $arguments = array(
          '--appName'  => $this->appName
      );
      $startInput = new ArrayInput($arguments);
      $command->run($startInput, $output);
    }
}
