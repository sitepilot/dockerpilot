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

class WPInstallCommand extends Command
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
        $this->setName('wp:install')
             ->setDescription('Install WordPress in an app.')
             ->setHelp('This command installs WordPress in an app.')
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
        if($this->installWP($output)) {
            $output->writeln('<info>WordPress isntalled!</info>');
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
            $installApps = array();

            // Check which apps are not running
            foreach($apps as $dir=>$app) {
                $env = sp_get_env($dir);
                if($appName = $env['APP_NAME']) {
                    $id = sp_get_container_id("sp-app-".$appName);

                    if($id) {
                        $installApps[] = $app;
                    }
                }
            }
            if(count($installApps) > 0) {
                if( ! $input->getOption('appName') ) {
                    // ask for appication
                    $question = new ChoiceQuestion(
                        'In which app would you like to install WordPress?',
                        $installApps, 0
                    );
                    $question->setErrorMessage('App %s is invalid.');
                    $this->appName = $questionHelper->ask($input, $output, $question);
                } else {
                    $this->appName = $input->getOption('appName');
                }

                $this->appDir  = sp_path(SERVER_APP_DIR . '/' . $this->appName);

                return true;
            } else {
                $output->writeln("<info>No apps are running, start an app to install WordPress.</info>");
            }
        } else {
            $output->writeln("<error>Couldn't find any apps, create or install an app!</error>");
        }

        return false;
    }

    protected function installWP($output)
    {
      $env = sp_get_env($this->appDir);
      $wpConfigFile = $this->appDir.'/app/wp-config.php';

      if(! file_exists($wpConfigFile)) {
        if(! empty($env['APP_DB_DATABASE']) && ! empty($env['APP_DB_USER']) && ! empty($env['APP_DB_HOST']) && ! empty($env['APP_DB_USER_PASSWORD'])) {

          $dbName = $env['APP_DB_DATABASE'];
          $dbHost = $env['APP_DB_HOST'];
          $dbUser = $env['APP_DB_USER'];
          $dbPass = $env['APP_DB_USER_PASSWORD'];
          $container = 'sp-app-'.$env['APP_NAME'];
          $containerID = sp_get_container_id($container);

          if($containerID) {
            $command1 = "docker exec --user serverpilot $container wp core download --path=/var/www/html";
            $command2 = 'docker exec --user serverpilot '.$container.' wp config create --path=/var/www/html --skip-check --dbname='.$dbName.' --dbhost='.$dbHost.' --dbuser='.$dbUser.' --dbpass='.$dbPass.' --dbprefix=sp_ --extra-php="\$_SERVER[\'HTTPS\'] = (! empty(\$_SERVER[\'HTTP_X_FORWARDED_PROTO\']) && \$_SERVER[\'HTTP_X_FORWARDED_PROTO\'] === \"https\" ? \"on\" : \"off\");"';

            $process1 = new Process($command1);
            $process2 = new Process($command2);

            try {
                $output->writeln('Downloading WordPress core...');
                $process1->mustRun();

                $output->writeln('Creating configuration...');
                $process2->mustRun();

                sp_change_env_var($this->appDir, 'APP_TEMPLATE', 'wordpress');

                return true;
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>".$e->getMessage()."</error>");
            }
          }
          return false;
        }
      } else {
        $output->writeln("<error>WordPress is already installed in: $this->appName.</error>");
      }

    }
}
