<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerpilotCommand extends Command
{
    protected $app = '';
    protected $appDir = '';

    /**
     * Get Docker secret ID by name.
     *
     * @param $name
     * @return string
     * @throws Exception
     */
    protected function getDockerSecretID($name)
    {
        $process = new Process("docker secret ls -f name=$name -q");

        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get Docker service ID by name.
     *
     * @param $name
     * @return string
     * @throws Exception
     */
    protected function getDockerServiceID($name)
    {
        $process = new Process("docker service ls --filter name=$name -q");

        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get swarm nodes.
     *
     * @return array
     * @throws Exception
     */
    protected function getDockerNodes()
    {
        $process = new Process("docker node ls -q");

        try {
            $process->mustRun();
            $nodes = explode(PHP_EOL, $process->getOutput() );
            $return = [];
            foreach($nodes as $node) {
                if(!empty($node)) {
                    $process = new Process("docker node inspect $node");
                    try {
                        $process->mustRun();
                        $node = json_decode($process->getOutput());
                        reset($node);
                        $return[] = $node[0]->Description->Hostname;
                    } catch (ProcessFailedException $e) {
                        throw new Exception($e->getMessage());
                    }
                }
            }
            return $return;
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Ask user for an app.
     *
     * @return array $app
     * @throws Exception
     */
    public function askForApp($input, $output, $questionName, $state = false)
    {
        $apps = dp_get_apps();
        $questionHelper = $this->getHelper('question');

        if (is_array($apps) && count($apps) > 0) {
            $questionApps = array();

            // Check app state
            foreach ($apps as $dir => $app) {
                $id = $this->getDockerServiceID( $app );
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

                $appsConfig = dp_get_config('apps');
                $this->appDir = dp_path( $appsConfig['configPath'] . '/' . $this->app);
                return true;
            } else {
                switch ($state) {
                    case 'running':
                        throw new Exception("All apps are stopped.");
                        break;
                    case 'stopped':
                        throw new Exception("All apps are running.");
                        break;
                }
            }
        }
        throw new Exception("No apps found, create a new app with app:create.");
    }
}
