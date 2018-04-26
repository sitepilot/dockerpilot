<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use UptimeRobot\API;

class AppStartCommand extends DockerpilotCommand
{
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
            ->addOption('app', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->userInput($input, $output);
            $this->generateFiles($output);
            $this->startApp($output);
            $this->reloadProxy($output);
            $this->setupMonitor($output);
            $output->writeln('<info>App started!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start application: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to start?', 'stopped');
    }

    /**
     * Generate application files based on template.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function generateFiles(OutputInterface $output)
    {
        // Get app environment
        $serverConfig = dp_get_config('server');

        if (isset($this->appConfig['name']) && isset($this->appConfig['user'])) {
            $output->writeln("Generating app configuration...");

            $bladeFolder = SERVER_WORKDIR . '/stacks/app';
            $cache = SERVER_WORKDIR . '/cache';
            $views = dp_path($bladeFolder);
            $blade = new Blade($views, $cache);

            $generateConfig = ['app', 'nginx', 'nginx-ssl'];
            foreach ($generateConfig as $config) {
                $configPath = $bladeFolder . '/' . $config . '.blade.php';
                $writeFile = true;

                if (file_exists($configPath)) {
                    if ($config == 'app') {
                        $destFile = dp_path($this->appDir . '/app.yml');
                    } elseif ($config == 'nginx-ssl') {
                        $checkConfig = $serverConfig['storagePath'] . '/config/letsencrypt/nginx/' . $this->appConfig['name'] . '.ssl.conf';
                        if(! file_exists($checkConfig)) {
                            $writeFile = false;
                        }
                        $destFile = dp_path($serverConfig['storagePath'] . '/config/nginx/' . $this->appConfig['name'] . '.ssl.conf');
                    } else {
                        $destFile = dp_path($serverConfig['storagePath'] . '/config/' . $config . '/' . $this->appConfig['name'] . '.conf');
                    }

                    if($writeFile) {
                        $content = $blade->view()->make($config,
                            ['app' => $this->appConfig, 'server' => $serverConfig])->render();
                        $writeFile = fopen($destFile, "w") or die("Unable to open file!");
                        fwrite($writeFile, $content);
                        fclose($writeFile);
                    }
                } else {
                    throw new Exception('Can\'t find ' . $config . '.blade.php in stack folder.');
                }
            }

            $output->writeln("Creating application storage directories on host...");

            $logDir = $serverConfig['storagePath'] . '/users/' . $this->appConfig['user'] . '/log/' . $this->appConfig['name'];
            $publicDir = $serverConfig['storagePath'] . '/users/' . $this->appConfig['user'] . '/apps/' . $this->appConfig['name'] . '/public';
            $tmpDir = $serverConfig['storagePath'] . '/users/' . $this->appConfig['user'] . '/tmp/' . $this->appConfig['name'];

            if ($serverConfig['useAnsible'] == 'true') {
                $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/createAppDir.yml --extra-vars "host=' . $this->appConfig['host'] . ' serverUser=' . $serverConfig['user'] . ' publicDir=' . $publicDir . ' logDir=' . $logDir . ' tmpDir=' . $tmpDir . '"');
                $process->setTimeout(3600);

                try {
                    $process->mustRun();
                    echo $process->getOutput();
                } catch (ProcessFailedException $e) {
                    throw new Exception($e->getMessage());
                }
            } else {
                try {
                    if (!file_exists($logDir)) {
                        mkdir($logDir, 0750, true);
                    }
                    if (!file_exists($publicDir)) {
                        mkdir($publicDir, 0750, true);
                    }
                    if (!file_exists($tmpDir)) {
                        mkdir($tmpDir, 0750, true);
                    }
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        } else {
            throw new Exception('Not all necessary app variables are set, aborting.');
        }
    }

    /**
     * Starts the app.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startApp(OutputInterface $output)
    {
        $process = new Process('cd ' . $this->appDir . ' && docker stack deploy -c app.yml ' . $this->app);
        $process->setTimeout(3600);

        try {
            $output->writeln("Starting app " . $this->app . ", please wait...");
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Reload proxy config.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function reloadProxy(OutputInterface $output)
    {
        $server = dp_get_config('server');
        if (!$server['useAnsible'] == 'true') {
            $proxyID = $this->getDockerContainerID('server_proxy');
            if (!empty($proxyID)) {
                $process = new Process("docker exec $proxyID dp-reload");
                $process->setTimeout(3600);

                try {
                    $output->writeln("Reloading proxy servers, please wait...");
                    $process->mustRun();
                } catch (ProcessFailedException $e) {
                    throw new Exception($e->getMessage());
                }
            } else {
                throw new Exception("Can't reload proxy, unknown proxy container.");
            }
        }
    }

    /**
     * Create app monitor in UptimeRobot.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function setupMonitor(OutputInterface $output)
    {
        $uptimeRobot = dp_get_config('uptimeRobot');

        if (!empty($uptimeRobot['apiKey']) && !empty($uptimeRobot['contactID']) && !empty($this->appConfig['monitor']['domain'])) {
            $output->writeln('Setting up monitor...');
            try {
                // Check if monitor already exists
                $config = [
                    'apiKey' => $uptimeRobot['apiKey'],
                    'url' => 'https://api.uptimerobot.com'
                ];
                $api = new API($config);
                $result = $api->request('/getMonitors');
                if (!empty($result['stat']) && $result['stat'] == 'fail') {
                    $output->writeln('<error> Setup monitor failed: ' . $result['message'] . '.</error>');
                } else {
                    $found = false;

                    foreach ($result['monitors']['monitor'] as $monitor) {
                        if ($monitor['friendlyname'] == $this->appConfig['name']) {
                            $found = $monitor['id'];
                            $foundDomain = $monitor['url'];
                        }
                    }

                    if (!$found) {
                        $output->writeln('Monitor not found, creating a new monitor...');
                        $args = [
                            'monitorFriendlyName' => $this->appConfig['name'],
                            'monitorUrl' => $this->appConfig['monitor']['domain'],
                            'monitorType' => 1,
                            'monitorAlertContacts' => $uptimeRobot['contactID']
                        ];

                        $result = $api->request('/newMonitor', $args);
                        if (!empty($result['stat']) && $result['stat'] == 'fail') {
                            $output->writeln('<error>Setup monitor failed: ' . $result['message'] . '.</error>');
                        }
                    } else {
                        if ($foundDomain != $this->appConfig['monitor']['domain']) {
                            $output->writeln('Monitor found, updating monitor...');
                            $args = [
                                'monitorID' => $found,
                                'monitorUrl' => $this->appConfig['monitor']['domain']
                            ];

                            $result = $api->request('/editMonitor', $args);
                            if (!empty($result['stat']) && $result['stat'] == 'fail') {
                                $output->writeln('<error>Updating monitor failed: ' . $result['message'] . '.</error>');
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}