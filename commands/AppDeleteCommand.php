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

class AppDeleteCommand extends Command
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
        $this->setName('app:delete')
             ->setDescription('Delete an application.')
             ->setHelp('This command deletes an application.')
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
            if($this->deleteApp($output)) {
                $output->writeln('<info>App deleted!</info>');
            }
        }
    }

    /**
     * Ask user which app needs to be deleted.
     *
     * @return bool
     */
    protected function userInput($input, $output)
    {
        $questionHelper = $this->getHelper('question');
        $apps = sp_get_apps();

        if(is_array($apps) && count($apps) > 0) {
            $deleteApps = array();

            // Check which apps are not running
            foreach($apps as $dir=>$app) {
                $env = sp_get_env($dir);
                if($appName = $env['APP_NAME']) {
                    $id = sp_get_container_id("sp-app-".$appName);

                    if(! $id) {
                        $deleteApps[] = $appName;
                    }
                }
            }
            if(count($deleteApps) > 0) {
                if( ! $input->getOption('appName') ) {
                    // ask for appication
                    $question = new ChoiceQuestion(
                        'Which app would you like to delete?',
                        $deleteApps, 0
                    );
                    $question->setErrorMessage('App %s is invalid.');
                    $this->appName = $questionHelper->ask($input, $output, $question);
                } else {
                    $this->appName = $input->getOption('appName');
                }

                $this->appDir  = sp_path(SERVER_APP_DIR . '/' . $this->appName);

                return true;
            } else {
                $output->writeln("<info>All apps are running, you can only delete stopped apps.</info>");
            }
        } else {
            $output->writeln("<error>Couldn't find any apps, create or install an app!</error>");
        }

        return false;
    }

    /**
     * Delete the application.
     *
     * @return bool
     */
    protected function deleteApp($output)
    {
      $dbContainer = sp_get_container_id('sp-db');

      if($dbContainer) {
        // Read environment file
        $env = sp_get_env($this->appDir);

        // Check if we need to delete a database table and user
        if(! empty($env['APP_DB_USER']) && ! empty($env['APP_DB_DATABASE']))
        {
          // Remove database table and user (if exists)
          $output->writeln('Removing database table and user...');

          $dbName = $env['APP_DB_DATABASE'];
          $dbUser = $env['APP_DB_USER'];

          $command = "docker exec sp-db bash -c \"MYSQL_PWD=".MYSQL_ROOT_PASSWORD." mysql -u root -e ".'\"'."DROP DATABASE IF EXISTS $dbName; GRANT USAGE ON *.* TO '$dbUser'@'%' IDENTIFIED BY 'dummypass'; DROP USER '$dbUser'@'%';".'\"'."\"";
          $process = new Process($command);
          try {
            $process->mustRun();
          } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
          }
        }

        return sp_rmdir($this->appDir);
      } else {
        $output->writeln("<error>Can't connect to database. Please start the server with `sp server:start`.</error>");
      }

      return false;
    }

}
