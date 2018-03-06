<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MailcatcherStartCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mailcatcher:start')
            ->setDescription('Starts Mailcatcher.')
            ->setHelp('This command starts Mailcatcher.');
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
            $this->startMailcatcher($output);
            $output->writeln("<info>Mailcatcher started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start Mailcatcher: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Create config.
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function createConfig(OutputInterface $output)
    {
        // Create docker-compose file
        $output->writeln("Generating docker-compose file...");
        $filePath = SERVER_WORKDIR . '/server/mailcatcher/config/docker-compose.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/server/mailcatcher/config';
        $cache = SERVER_WORKDIR . '/../data/cache';
        $views = dp_path($bladeFolder);

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('docker-compose')->render();
            $destFile = dp_path(SERVER_WORKDIR . '/server/mailcatcher/docker-compose.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Starts Mailcatcher.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startMailcatcher(OutputInterface $output)
    {
        $output->writeln("Starting Mailcatcher, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/server/mailcatcher && docker-compose up -d');

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
