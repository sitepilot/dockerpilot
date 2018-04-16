<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
            $this->startApp($input, $output);
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
        $appConfig = dp_get_app_config($this->appDir, 'all');
        $appsConfig = dp_get_config('apps');
        $serverConfig = dp_get_config('server');

        if (isset($appConfig['name']) && isset($appConfig['stack'])) {
            $output->writeln("Generating app configuration...");

            $bladeFolder = SERVER_WORKDIR . '/stacks/' . $appConfig['stack'];
            $cache = SERVER_WORKDIR . '/data/cache';
            $views = dp_path($bladeFolder);

            $filePath = $bladeFolder . '/app.blade.php';
            if (file_exists($filePath)) {
                $blade = new Blade($views, $cache);
                $content = $blade->view()->make('app', ['app' => $appConfig, 'apps' => $appsConfig, 'server' => $serverConfig])->render();
                $destFile = dp_path($this->appDir . '/app.yml');
                $writeFile = fopen($destFile, "w") or die("Unable to open file!");
                fwrite($writeFile, $content);
                fclose($writeFile);
            } else {
                throw new Exception('Can\'t find app.blade.php in stack folder.');
            }

            // Create app storage folder
            $server = dp_get_config('server');
            $apps = dp_get_config('apps');

            $output->writeln("Creating application storage dir on host...");
            $appStorageDir = $apps['storagePath'] . '/' . $this->app;
            if($server['useAnsible'] == 'true') {
                $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/createAppDir.yml --extra-vars "host=' . $appConfig['host'] . ' app_dir=' . $appStorageDir . '"');
                $process->setTimeout(3600);

                try {
                    $process->mustRun();
                    echo $process->getOutput();
                } catch (ProcessFailedException $e) {
                    throw new Exception($e->getMessage());
                }
            } else {
                try {
                    mkdir($appStorageDir . '/logs', 0750, true);
                    mkdir($appStorageDir . '/data', 0750, true);
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startApp(InputInterface $input, OutputInterface $output)
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
}