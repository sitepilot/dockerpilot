<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RoundcubeStartCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('roundcube:start')
            ->setDescription('Starts Roundcube webmail.')
            ->setHelp('This command starts Roundcube webmail.');
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
            $this->startRoundcube($output);
            $output->writeln("<info>Roundcube started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start Roundcube: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Create Roundcube storage directory.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function createPaths(OutputInterface $output)
    {
        $output->writeln("Creating Roundcube storage path...");
        $roundcube = dp_get_config('roundcube');

        try {
            if (!file_exists(dp_path($roundcube['storagePath']))) {
                mkdir(dp_path($roundcube['storagePath']), 0750, true);
            }
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Create Roundcube configuration.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating Roundcube stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/roundcube/roundcube.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/roundcube';
        $cache = SERVER_WORKDIR . '/data/cache';
        $views = dp_path($bladeFolder);
        $roundcube = dp_get_config('roundcube');

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('roundcube', ['roundcube' => $roundcube])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/stacks/roundcube/config/roundcube.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Start Roundcube.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startRoundcube(OutputInterface $output)
    {
        $output->writeln("Starting Roundcube, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/stacks/roundcube/config && docker stack deploy -c roundcube.yml roundcube');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
