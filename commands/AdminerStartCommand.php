<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminerStartCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('adminer:start')
            ->setDescription('Starts Adminer.')
            ->setHelp('This command starts adminer.');
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
            $this->startAdminer($output);
            $output->writeln("<info>Adminer started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start adminer: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Create Adminer configuration.
     *
     * @param $output
     * @return void
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating Adminer stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/adminer/adminer.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/adminer';
        $cache = SERVER_WORKDIR . '/data/cache';
        $views = dp_path($bladeFolder);
        $adminer = dp_get_config('adminer');

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('adminer', ['adminer' => $adminer])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/stacks/adminer/config/adminer.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Start Adminer.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startAdminer(OutputInterface $output)
    {
        $output->writeln("Starting Adminer, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/stacks/adminer/config && docker stack deploy -c adminer.yml adminer');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
