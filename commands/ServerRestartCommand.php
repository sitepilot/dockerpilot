<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ServerRestartCommand extends ServerpilotCommand
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
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->restartServer($output)){
            $output->writeln('<info>Server restarted!</info>');
        }
    }

    /**
     * Restart the server.
     *
     * @return bool
     */
    protected function restartServer($output) {
      $arguments = array();
      $input = new ArrayInput($arguments);

      $command = $this->getApplication()->find('server:stop');
      $command->run($input, $output);

      $command = $this->getApplication()->find('server:start');
      $command->run($input, $output);

      return true;
    }
}
