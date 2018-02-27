<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DockerpilotCommand extends Command
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
   * Ask user for an app.
   *
   * @return array $app
   */
  public function askForApp($input, $output, $questionName, $state = false)
  {
    $apps = sp_get_apps();
    $returnApp = array();
    $questionHelper = $this->getHelper('question');

    if(is_array($apps) && count($apps) > 0) {
      $questionApps = array();

      // Check app state
      foreach($apps as $dir=>$app) {
          $env = sp_get_env($dir);
          if($appName = $env['APP_NAME']) {
              $id = sp_get_container_id("dp-app-".$appName);
              switch ($state) {
                case 'running':
                  if($id) { $questionApps[] = $appName; }
                  break;
                case 'stopped':
                  if(!$id) { $questionApps[] = $appName; }
                  break;
                default:
                  $questionApps[] = $appName;
                  break;
              }
          }
      }

      if(count($questionApps) > 0) {
          if( ! $input->getOption('appName') ) {
              // Ask for appication
              $question = new ChoiceQuestion(
                  $questionName,
                  $questionApps, 0
              );
              $question->setErrorMessage('App %s is invalid.');
              $this->appName = $questionHelper->ask($input, $output, $question);
          } else {
              $this->appName = $input->getOption('appName');
          }

          $this->appDir  = sp_path(SERVER_APP_DIR . '/' . $this->appName);
          return true;
      } else {
          switch ($state) {
            case 'running':
              $output->writeln("<info>All apps are stopped.</info>");
              break;
            case 'stopped':
              $output->writeln("<info>All apps are running.</info>");
              break;
            default:
              $output->writeln("<info>No apps found, create a new app with app:create.</info>");
              break;
          }
      }
    }

    return false;
  }

}
