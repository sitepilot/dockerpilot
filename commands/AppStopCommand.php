<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AppStopCommand extends ServerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:stop')
             ->setDescription('Stop an app.')
             ->setHelp('This command stops an app.')
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
            if($this->stopApp($output)) {
                $output->writeln('<info>App stopped!</info>');
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
        return $this->askForApp($input, $output, 'Which app would you like to stop?', 'running');
    }

    /**
     * Stops the app.
     *
     * @return bool
     */
    protected function stopApp($output) {
        $output->writeln("Stopping app ".$this->appName.", please wait...");

        // Run stop command (if exists)
        if(file_exists($this->appDir.'/interface.php')){
            require_once $this->appDir.'/interface.php';
            $appInterfaceClass = '\Serverpilot\App\\'.ucfirst($this->appName).'\AppInterface';
            if(method_exists($appInterfaceClass, 'stop')){
                $appInterfaceClass::stop($output);
            }
        }

        $process = new Process('cd '.$this->appDir.' && docker-compose down');

        try {
            $process->mustRun();

            $process = new Process('cd '.$this->appDir.' && docker-compose rm');

            try {
                $process->mustRun();
                return true;
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>".$e->getMessage()."</error>");
            }
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }

}
