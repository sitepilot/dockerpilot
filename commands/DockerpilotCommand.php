<?php

namespace Dockerpilot\Command;

use Exception;
use Maknz\Slack\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerpilotCommand extends Command
{
    protected $app = '';
    protected $appDir = '';
    protected $appConfig = '';

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
    public function askForApp($input, $output, $questionName, $state = false, $stack = false)
    {
        $apps = $this->getApps($state, $stack);
        $questionHelper = $this->getHelper('question');

        if (count($apps) > 0) {
            if (!$input->getOption('app')) {
                // Ask for appication
                $question = new ChoiceQuestion(
                    $questionName,
                    $apps, 0
                );
                $question->setErrorMessage('App %s is invalid.');
                $this->app = $questionHelper->ask($input, $output, $question);
            } else {
                $this->app = $input->getOption('app');
            }

            $appsConfig = dp_get_config('apps');
            $this->appDir = dp_path( $appsConfig['configPath'] . '/' . $this->app);
            $this->appConfig = dp_get_app_config($this->appDir);
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

        throw new Exception("No apps found, create a new app with app:create.");
    }

    public function getApps($state = false, $stack = false)
    {
        $apps = dp_get_apps();
        $returnApps = [];

        foreach ($apps as $dir => $app) {
            $id = $this->getDockerServiceID( $app );
            switch ($state) {
                case 'running':
                    if ($id) {
                        $returnApps[] = $app;
                    }
                    break;
                case 'stopped':
                    if (!$id) {
                        $returnApps[] = $app;
                    }
                    break;
                default:
                    $returnApps[] = $app;
                    break;
            }
        }

        if($stack) {
            $apps = dp_get_config('apps');
            $returnStackApps = [];
            foreach($returnApps as $app) {
                $appConfig = dp_get_app_config($apps['configPath'] . '/' . $app);
                if($appConfig['stack'] == $stack) {
                    $returnStackApps[] = $app;
                }
            }
            $returnApps = $returnStackApps;
        }

        return $returnApps;
    }

    /**
     * Send a simple slack message.
     *
     * @param $title
     * @param $text
     * @param array $params
     */
    public function slackSendAttachment($title, $text, $params = array())
    {
        $slackConfig = dp_get_config('slack');

        if(!empty($slackConfig['webhook']) && !empty($slackConfig['channel'])) {
            $client = new Client($slackConfig['webhook']);
            $defaults = [
                'channel' => $slackConfig['channel'],
                'color' => 'success',
                'toText' => true
            ];

            if(!is_array($text)){
                $params = $params + $defaults;
                $client->to($params['channel'])->attach([
                    'fallback' => $title,
                    'text' => $text,
                    'color' => $params['color'],
                ])->send($title);
            } else {
                $client = $client->to($defaults['channel']);
                foreach($text as $item) {
                    $params = (isset($item['params']) && is_array($item['params']) ? $item['params'] : []);
                    $params = $params + $defaults;
                    $client = $client->attach([
                        'fallback' => $title,
                        'text' => $item['text'],
                        'color' => $params['color'],
                    ]);
                }
                $client->send($title);
            }
        }
    }

    public function notify(OutputInterface $output, $title, $content = '', $slack = false, $color = '#3498db') {
        if(is_object($content)) {
            $content = $content->getMessage();
        }

        $title = (! empty($this->app) ? '[' . $this->app . '] ' : '') . $title;
        $contentOutput = (! empty($this->app) && ! empty($content) ? '[' . $this->app . '] ' : '') . $content;

        if(! empty($title) && empty($contentOutput)) $output->writeln($title );
        if(! empty($contentOutput)) $output->writeln($contentOutput);

        if($slack) {
            $this->slackSendAttachment(
                $title,
                $content,
                ['color' => $color]
            );
        }
    }
}
