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

class AppStopCommand extends Command
{
    /**
     * The app name.
     *
     * @var string
     */
    protected $appName = '';

    /**
     * The app dir.
     *
     * @var string
     */
    protected $appDir = '';

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
        $questionHelper = $this->getHelper('question');
        $apps = sp_get_apps();

        if(is_array($apps) && count($apps) > 0) {
            $stopApps = array();

            // Check which apps are not running
            foreach($apps as $dir=>$app) {
                $env = sp_get_env($dir);
                if($appName = $env['APP_NAME']) {
                    $id = sp_get_container_id("sp-app-".$appName);

                    if($id) {
                        $stopApps[] = $app;
                    }
                }
            }
            if(count($stopApps) > 0) {
                if( ! $input->getOption('appName') ) {
                    // ask for appication
                    $question = new ChoiceQuestion(
                        'Which app would you like to stop?',
                        $stopApps, 0
                    );
                    $question->setErrorMessage('App %s is invalid.');
                    $this->appName = $questionHelper->ask($input, $output, $question);
                } else {
                    $this->appName = $input->getOption('appName');
                }

                $this->appDir  = sp_path(SERVER_APP_DIR . '/' . $this->appName);

                return true;
            } else {
                $output->writeln("<info>No apps are running.</info>");
            }
        } else {
            $output->writeln("<error>Couldn't find any apps, create or install an app!</error>");
        }

        return false;
    }

    /**
     * Stops the app.
     *
     * @return bool
     */
    protected function stopApp($output) {
        $output->writeln("Stopping app ".$this->appName.", please wait...");
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
