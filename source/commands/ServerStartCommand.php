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

class ServerStartCommand extends Command
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
            ->setHelp('This command starts the server.')
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
            $this->createNetwork($output);
            $this->createConfig($output);
            $this->startServer($input, $output);
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
        $process = new Process('docker network create dockerpilot');
        $process->run();
    }

    /**
     * Create server configuration.
     *
     * @param $output
     * @return void
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating docker-compose file...");
        $filePath = SERVER_WORKDIR . '/server/config/docker-compose.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/server/config';
        $cache = SERVER_WORKDIR . '/../data/cache';
        $views = dp_path($bladeFolder);

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('docker-compose')->render();
            $destFile = dp_path(SERVER_WORKDIR . '/server/docker-compose.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Starts the server.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startServer(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting server, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/server && docker-compose up -d');
        $process->setTimeout(3600);

        try {
            if ($input->getOption('build')) {
                $output->writeln("Building server, please wait...");
                $buildProcess = new Process('cd ' . SERVER_WORKDIR . '/server && docker-compose build --no-cache');
                $buildProcess->setTimeout(3600);
                $buildProcess->mustRun();
            }

            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
