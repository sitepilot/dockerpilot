<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
      $arguments = array(
          '--app'  => $this->app
      );
      $input = new ArrayInput($arguments);

      $command = $this->getApplication()->find('app:stop');
      $command->run($input, $output);

      $command = $this->getApplication()->find('app:start');
      $command->run($input, $output);

      return true;
    }
}
