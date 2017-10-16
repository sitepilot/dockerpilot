<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Philo\Blade\Blade;

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
             ->setHelp('This command starts the server.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->createNetwork($output)) {
            if($this->createConfig($output)) {
                if($this->startServer($output)) {
                    $output->writeln("<info>Server started!</info>");
                }
            }
        }
    }

    /**
     * Create network.
     *
     * @return bool
     */
    protected function createNetwork($output)
    {
        $output->writeln("Creating network (serverpilot)...");
        $process = new Process('docker network create serverpilot');

        $process->run();

        return true;
    }

    /**
     * Create server configuration.
     *
     * @return bool
     */
    protected function createConfig($output)
    {
        // Create docker-compose file
        $output->writeln("Generating docker-compose file...");
        $filePath = SERVER_WORKDIR.'/server/config/docker-compose.blade.php';

        $bladeFolder = SERVER_WORKDIR.'/server/config';
        $cache = SERVER_WORKDIR . '/cache';
        $views = sp_path($bladeFolder);

        if(file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('docker-compose')->render();
            $destFile = sp_path(SERVER_WORKDIR.'/server/docker-compose.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }

        return true;
    }

    /**
     * Starts the server.
     *
     * @return bool
     */
    protected function startServer($output)
    {
        $output->writeln("Starting server, please wait...");
        $process = new Process('cd server && docker-compose up -d');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
