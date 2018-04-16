<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PortainerStartCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('portainer:start')
            ->setDescription('Starts Portainer.')
            ->setHelp('This command starts Portainer.');
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
            $this->startPortainer($output);
            $output->writeln("<info>Portainer started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start Portainer: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Create Portainer storage directory.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function createPaths(OutputInterface $output)
    {
        $output->writeln("Creating Portainer storage path...");
        $portainer = dp_get_config('portainer');

        try {
            if (!file_exists(dp_path($portainer['storagePath']))) {
                mkdir(dp_path($portainer['storagePath']), 0750, true);
            }
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Create Portainer configuration.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating Portainer stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/portainer/portainer.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/portainer';
        $cache = SERVER_WORKDIR . '/data/cache';
        $views = dp_path($bladeFolder);
        $portainer = dp_get_config('portainer');

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('portainer', ['portainer' => $portainer])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/stacks/portainer/config/portainer.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Start Portainer.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startPortainer(OutputInterface $output)
    {
        $output->writeln("Starting Portainer, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/stacks/portainer/config && docker stack deploy -c portainer.yml portainer');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
