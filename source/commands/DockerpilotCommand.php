<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DockerpilotCommand extends Command
{
    /**
     * The app name.
     *
     * @var string
     */
    protected $app = '';

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
     * @throws Exception
     */
    public function askForApp($input, $output, $questionName, $state = false)
    {
        $apps = dp_get_apps();
        $returnApp = array();
        $questionHelper = $this->getHelper('question');

        if (is_array($apps) && count($apps) > 0) {
            $questionApps = array();

            // Check app state
            foreach ($apps as $dir => $app) {
                $env = dp_get_env($dir);
                if ($app = $env['APP_NAME']) {
                    $id = dp_get_container_id("dp-app-" . $app);
                    switch ($state) {
                        case 'running':
                            if ($id) {
                                $questionApps[] = $app;
                            }
                            break;
                        case 'stopped':
                            if (!$id) {
                                $questionApps[] = $app;
                            }
                            break;
                        default:
                            $questionApps[] = $app;
                            break;
                    }
                }
            }

            if (count($questionApps) > 0) {
                if (!$input->getOption('app')) {
                    // Ask for appication
                    $question = new ChoiceQuestion(
                        $questionName,
                        $questionApps, 0
                    );
                    $question->setErrorMessage('App %s is invalid.');
                    $this->app = $questionHelper->ask($input, $output, $question);
                } else {
                    $this->app = $input->getOption('app');
                }

                $this->appDir = dp_path(SERVER_APP_DIR . '/' . $this->app);
                return true;
            } else {
                switch ($state) {
                    case 'running':
                        throw new Exception("All apps are stopped.");
                        break;
                    case 'stopped':
                        throw new Exception("All apps are running.");
                        break;
                    default:
                        throw new Exception("No apps found, create a new app with app:create.");
                        break;
                }
            }
        }
    }

}
