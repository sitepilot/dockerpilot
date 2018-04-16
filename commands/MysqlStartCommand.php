<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MysqlStartCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mysql:start')
            ->setDescription('Starts the mysql server.')
            ->setHelp('This command starts the mysql server.');
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
            $this->createPaths($output);
            $this->createConfig($output);
            $this->startServer($input, $output);
            $output->writeln("<info>MySQL server started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start the MySQL server: \n" . $e->getMessage() . "</error>");
        }
    }

    protected function createPaths(OutputInterface $output) {
        $output->writeln("Creating MySQL storage path...");

        $server = dp_get_config('server');
        $mysql = dp_get_config('mysql');

        if($server['useAnsible'] == 'true') {
            // Create mysql folder on every host
            $process = new Process('ansible dockerpilot -m file -a "path=' . $mysql['storagePath'] . ' state=directory"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            // Create mysql folder
            try {
                if (!file_exists(dp_path($mysql['storagePath']))) {
                    mkdir(dp_path($mysql['storagePath']), 0750, true);
                }
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Create MySQL server configuration.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating MySQL stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/mysql/mysql.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/mysql';
        $cache = SERVER_WORKDIR . '/data/cache';
        $views = dp_path($bladeFolder);
        $mysql = dp_get_config('mysql');

        if(empty($this->getDockerSecretID($mysql['rootPassSecret'])) || empty($this->getDockerSecretID($mysql['userPassSecret'])) || empty($this->getDockerSecretID($mysql['userNameSecret']))) {
            throw new Exception("Could not find all docker secrets, please configure them first.");
        }

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('mysql', ['mysql' => $mysql])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/stacks/mysql/config/mysql.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Starts the MySQL server.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startServer(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting MySQL server, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/stacks/mysql/config && docker stack deploy -c mysql.yml mysql');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
