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

class WPUpdateCommand extends ServerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('wp:update')
             ->setDescription('Update WordPress in an app.')
             ->setHelp('This command updates WordPress in an app.')
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
        if($this->updateWP($output)) {
            $output->writeln('<info>WordPress updated!</info>');
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
        return $this->askForApp($input, $output, 'In which app would you like to update WordPress?', 'running');
    }

    /**
     * Update WordPress in app directory.
     *
     * @return bool
     */
    protected function updateWP($output)
    {
      $env = sp_get_env($this->appDir);
      $wpConfigFile = $this->appDir.'/app/wp-config.php';

      if(file_exists($wpConfigFile) && $env['APP_TEMPLATE'] == 'wordpress') {
        $container = 'sp-app-'.$env['APP_NAME'];
        $containerID = sp_get_container_id($container);

        if($containerID) {
          $command1 = "docker exec --user serverpilot $container wp core update --path=/var/www/html";
          $process1 = new Process($command1);

          $command2 = "docker exec --user serverpilot $container wp plugin list --format=json --path=/var/www/html";
          $process2 = new Process($command2);

          try {
              $output->writeln('Updating WordPress core in app: '.$this->appName.'...');
              $process1->mustRun();

              $output->writeln(trim($process1->getOutput()));

              $process2->mustRun();
              $plugins = json_decode($process2->getOutput());

              if(! empty($env['APP_WP_UPDATE_PLUGINS'])) {
                $updatePlugins = explode(',', $env['APP_WP_UPDATE_PLUGINS']);
                $updateList = '';

                if(is_array($plugins))
                {
                  foreach($plugins as $plugin) {
                    if(in_array($plugin->name, $updatePlugins)) {
                      $updateList .= ' '.$plugin->name;
                    }
                  }
                  if(! empty($updateList)){
                    $output->writeln('Updating plugins:'.$updateList);
                    $command3 = "docker exec --user serverpilot $container wp plugin update $updateList --path=/var/www/html";
                    $process3 = new Process($command3);
                    $process3->mustRun();
                    $output->writeln(trim($process3->getOutput()));
                  }
                }
              }

              sp_change_env_var($this->appDir, 'APP_WP_LAST_UPDATE', time());

              return true;
          } catch (ProcessFailedException $e) {
              $output->writeln("<error>".$e->getMessage()."</error>");
          }
        } else {
          $output->writeln("<error>Can't find application container ID.</error>");
        }
      } else {
        $output->writeln("<error>WordPress isn't installed in app: $this->appName.</error>");
      }
      return false;
    }
}
