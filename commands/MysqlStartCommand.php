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
            $this->createConfig($output);
            $this->startServer($input, $output);
            $output->writeln("<info>MySQL server started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start the MySQL server: \n" . $e->getMessage() . "</error>");
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
        $mysql = dp_get_config('mysql');
        $output->writeln("Checking secrets...");

        foreach($mysql['servers'] as $server) {
            if(empty($this->getDockerSecretID($server['rootPassSecret'])) || empty($this->getDockerSecretID($server['userPassSecret'])) || empty($this->getDockerSecretID($server['userNameSecret']))) {
                throw new Exception("Could not find all docker secrets, please configure them first.");
            }
        }

        $output->writeln("Generating MySQL stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/mysql/mysql.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/mysql';
        $cache = SERVER_WORKDIR . '/cache';
        $views = dp_path($bladeFolder);
        $server = dp_get_config('server');

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('mysql', ['mysql' => $mysql, 'server' => $server])->render();
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
