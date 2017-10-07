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

use Philo\Blade\Blade;

class AppStartCommand extends Command
{
    /**
     * The application name.
     *
     * @var string
     */
    protected $appName = '';

    /**
     * The application dir.
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
        $this->setName('app:start')
             ->setDescription('Start an application.')
             ->setHelp('This command starts an application.')
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
            if($this->generateFiles($output)){
                if($this->startApp($output)) {
                    $output->writeln('<info>App started!</info>');
                }
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
            $startApps = array();

            // Check which apps are not running
            foreach($apps as $dir=>$app) {
                $env = sp_get_env($dir);
                if($appName = $env['APP_NAME']) {
                    $id = sp_get_container_id("sp-app-".$appName);

                    if(!$id) {
                        $startApps[] = $app;
                    }
                }
            }
            if(count($startApps) > 0) {
                if( ! $input->getOption('appName') ) {
                    // ask for appication
                    $question = new ChoiceQuestion(
                        'Which app would you like to start?',
                        $startApps, 0
                    );
                    $question->setErrorMessage('App %s is invalid.');
                    $this->appName = $questionHelper->ask($input, $output, $question);
                } else {
                    $this->appName = $input->getOption('appName');
                }

                $this->appDir  = sp_path(SERVER_APP_DIR . '/' . $this->appName);

                return true;
            } else {
                $output->writeln("<info>All apps are running.</info>");
            }
        } else {
            $output->writeln("<error>Couldn't find any apps, create or install an app!</error>");
        }

        return false;
    }

    /**
     * Generate application files based on template.
     *
     * @since 1.0.0
     * @return bool
     */
    protected function generateFiles($output) {
        // Get app environment
        $env = sp_get_env($this->appDir);

        if(isset($env['APP_STACK'])) {
            $output->writeln("<info>Generating app configuration...</info>");

            $bladeFolder = SERVER_STACK_DIR.'/'.$env['APP_STACK'].'/config';
            $cache = SERVER_WORKDIR . '/cache';
            $views = sp_path($bladeFolder);

            $generate = ['docker-compose' => 'yml', 'php' => 'ini'];

            foreach($generate as $file=>$ext) {
                $filePath = $bladeFolder.'/'.$file.'.blade.php';
                if(file_exists($filePath)) {
                    $blade = new Blade($views, $cache);
                    $content = $blade->view()->make($file, ['env' => $env])->render();
                    $destFile = sp_path($this->appDir.'/'.$file.($ext ? '.'.$ext : ''));
                    $writeFile = fopen($destFile, "w") or die("Unable to open file!");
                    fwrite($writeFile, $content);
                    fclose($writeFile);
                }
            }
        }

        return true;
    }

    /**
     * Starts the app.
     *
     * @return bool
     */
    protected function startApp($output) {
        $output->writeln("Starting app ".$this->appName.", please wait...");
        $process = new Process('cd '.$this->appDir.' && docker-compose up -d');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }

}
