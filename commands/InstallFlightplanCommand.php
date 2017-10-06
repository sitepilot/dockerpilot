<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Composer\Console\Application;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InstallFlightplanCommand extends Command
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
        $this->setName('install:flightplan')
             ->setDescription('Install flightplan in an app stack.')
             ->setHelp('This command installs flightplan in an app stack.')
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
            if($this->installFlightplan($output)) {
                $output->writeln('<info>Flightplan initialized in app directory! Don\'t forget to update APP_MOUNT_POINT in the .env file to /var/www.</info>');
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

                    if(! $id) {
                        $stopApps[] = $app;
                    }
                }
            }
            if(count($stopApps) > 0) {
                if( ! $input->getOption('appName') ) {
                    // ask for appication
                    $question = new ChoiceQuestion(
                        'In which app would you like to install Flightplan?',
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
                $output->writeln("<info>All apps are running, you can't install Flightplan in a running app.</info>");
            }
        } else {
            $output->writeln("<error>Couldn't find any apps, create or install an app!</error>");
        }

        return false;
    }

    /**
     * Install Flightplan from Github.
     *
     * @return bool
     */
    protected function installFlightplan($output) {
        // Check if the install directory is empty
        $installDir = sp_path($this->appDir.'/app');
        $isDirEmpty = !(new \FilesystemIterator($installDir))->valid();
        if(! $isDirEmpty){
            $output->writeln('<error>The app directory ('.$installDir.') isn\'t empty.</error>');
            return false;
        }
        // Clone flightplan to app directory
        $output->writeln("Installing Flightplan in ".$this->appName.", please wait...");
        $process = new Process('cd '.$installDir.' && git clone git@github.com:sitepilot/flightplan.git .');

        try {
            $process->mustRun();

            $output->writeln("Updating composer packages, please wait...");
            $process = new Process('cd '.$installDir.' && php /usr/local/bin/composer update');

            try {
              $process->mustRun();

              $flightplanEnvFile = $installDir.'/serverpilot.env';
              if(file_exists($flightplanEnvFile)){
                $output->writeln("Updating app environment...");
                $flightplanEnv = file_get_contents($flightplanEnvFile);
                file_put_contents($this->appDir.'/.env', $flightplanEnv.PHP_EOL , FILE_APPEND | LOCK_EX);
              }

              return true;
            } catch (ProcessFailedException $e){
              $output->writeln("<error>".$e->getMessage()."</error>");
            }
        }
        catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }
        return false;
    }

}
