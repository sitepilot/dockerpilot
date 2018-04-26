<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ServerStartCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('server:start')
            ->setDescription('Starts the server.')
            ->setHelp('This command starts the server.');
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
            $this->createNetwork($output);
            $this->createFolders($output);
            $this->createConfig($output);
            $this->startServer($output);
            $output->writeln("<info>Server started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start the server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Create network.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function createNetwork(OutputInterface $output)
    {
        $output->writeln("Creating network (dockerpilot)...");
        $process = new Process('docker network create --driver=overlay dockerpilot');
        $process->run();
    }

    /**
     * Create server data folders.
     * @throws Exception
     */
    protected function createFolders(OutputInterface $output)
    {
        $output->writeln("Creating server storage directories on hosts...");
        $server = dp_get_config('server');
        $configFolders = ['apps', 'nginx', 'letsencrypt'];

        foreach ($configFolders as $folder) {
            $folder = $server['storagePath'] . '/config/' . $folder;
            if (!file_exists($folder)) {
                mkdir($folder, 0750, true);
            }
        }

        $usersDir = $server['storagePath'] . '/users';
        $mysqlDir = $server['storagePath'] . '/mysql';
        $backupDir = $server['storagePath'] . '/backup';

        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/createServerDir.yml --extra-vars "host=all serverUser=' . $server['user'] . ' usersDir=' . $usersDir . ' mysqlDir=' . $mysqlDir . ' backupDir=' . $backupDir . '"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            try {
                if (!file_exists($usersDir)) {
                    mkdir($usersDir, 0750, true);
                }
                if (!file_exists($mysqlDir)) {
                    mkdir($mysqlDir, 0750, true);
                }
                if (!file_exists($backupDir)) {
                    mkdir($backupDir, 0750, true);
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Create server configuration.
     *
     * @param $output
     * @return void
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/server/server.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/server';
        $cache = SERVER_WORKDIR . '/cache';
        $views = dp_path($bladeFolder);
        $server = dp_get_config('server');

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('server', ['server' => $server])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/stacks/server/config/server.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Starts the server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startServer(OutputInterface $output)
    {
        $output->writeln("Starting server, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/stacks/server/config && docker stack deploy -c server.yml server');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
