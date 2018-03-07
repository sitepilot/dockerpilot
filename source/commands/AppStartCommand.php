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
            ->addOption('app', null, InputOption::VALUE_OPTIONAL)
            ->addOption('build', null, InputOption::VALUE_NONE);
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
     */
    protected function generateFiles(OutputInterface $output)
    {
        // Get app environment
        $env = dp_get_env($this->appDir);

        if (isset($env['APP_STACK'])) {
            $output->writeln("Generating app configuration...");

            $bladeFolder = SERVER_STACK_DIR . '/' . $env['APP_STACK'] . '/config';
            $cache = SERVER_WORKDIR . '/../data/cache';
            $views = dp_path($bladeFolder);

            $generate = ['docker-compose' => 'yml', 'php' => 'ini', 'ssmtp' => 'conf'];

            if(SERVER_DOCKER_SYNC) {
                $generate['docker-sync'] = 'yml';
            }

            foreach ($generate as $file => $ext) {
                $filePath = $bladeFolder . '/' . $file . '.blade.php';
                if (file_exists($filePath)) {
                    $blade = new Blade($views, $cache);
                    $content = $blade->view()->make($file, ['env' => $env])->render();
                    $destFile = dp_path($this->appDir . '/' . $file . ($ext ? '.' . $ext : ''));
                    $writeFile = fopen($destFile, "w") or die("Unable to open file!");
                    fwrite($writeFile, $content);
                    fclose($writeFile);
                }
            }
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
        $process = new Process('cd ' . $this->appDir . ' && docker-compose up -d');
        $process->setTimeout(3600);

        try {

            if ($input->getOption('build')) {
                $output->writeln("Building app " . $this->app . ", please wait...");
                $buildProcess = new Process('cd ' . $this->appDir . ' && docker-compose build --no-cache app');
                $buildProcess->setTimeout(3600);
                $buildProcess->mustRun();
            }

            if(SERVER_DOCKER_SYNC) {
                $output->writeln("Starting docker-sync...");
                $syncProcess = new Process('cd ' . $this->appDir . ' && docker-sync start');
                $syncProcess->setTimeout(3600);
                $syncProcess->mustRun();
            }

            $output->writeln("Starting app " . $this->app . ", please wait...");
            $process->mustRun();

            if (file_exists($this->appDir . '/interface.php')) {
                require_once $this->appDir . '/interface.php';
                $appInterfaceClass = '\Dockerpilot\App\\' . ucfirst($this->app) . '\AppInterface';
                if (method_exists($appInterfaceClass, 'start')) {
                    $appInterfaceClass::start($output);
                }
            }
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
