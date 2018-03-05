<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SFTPStartCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('sftp:start')
            ->setDescription('Starts the SFTP server.')
            ->setHelp('This command starts the SFTP server.');
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
            $this->startServer($output);
            $output->writeln("<info>SFTP server started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start the SFTP server: \n" . $e->getMessage() . "</error>");
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
        $apps = dp_get_apps();
        $config = '';
        $sftpAppVolumes = '';

        // Get user and group id of current user (dockerpilot)
        if (!dp_is_windows()) {
            $process = new Process('id -u');
            $uID = trim($process->mustRun()->getOutput());
            $process = new Process('id -g');
            $gID = trim($process->mustRun()->getOutput());
        } else {
            $uID = 1000;
            $gID = 1000;
        }

        // Create users config
        $output->writeln("Generating users config file...");

        if (is_array($apps) && count($apps) > 0) {
            foreach ($apps as $dir => $app) {
                $env = dp_get_env($dir);
                if (!empty($env['APP_NAME']) && !empty($env['APP_SFTP_PASS'])) {
                    $config .= $env['APP_NAME'] . ":" . $env['APP_SFTP_PASS'] . ":" . $uID . ":" . $gID . "\n";
                    $sftpAppVolumes .= "        - " . $dir . (isset($env['APP_SFTP_DIR']) ? '/' . $env['APP_SFTP_DIR'] : '') . ":/home/" . $env['APP_NAME'] . "/public\n";
                }
            }
            $writeFile = SERVER_WORKDIR . '/server/sftp/users.conf';
            if (file_exists($writeFile)) {
                unlink($writeFile);
            }
            file_put_contents($writeFile, $config);
        }

        // Create docker-compose file
        $output->writeln("Generating docker-compose file...");
        $filePath = SERVER_WORKDIR . '/server/sftp/config/docker-compose.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/server/sftp/config';
        $cache = SERVER_WORKDIR . '/../data/cache';
        $views = dp_path($bladeFolder);

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('docker-compose', ['sftpAppVolumes' => $sftpAppVolumes])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/server/sftp/docker-compose.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Starts the SFTP server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startServer(OutputInterface $output)
    {
        $sftpID = dp_get_container_id('dp-sftp');

        // Stop container if running
        if ($sftpID) {
            $command = $this->getApplication()->find('sftp:stop');
            $command->run(new ArrayInput([]), $output);
        }

        $output->writeln("Starting SFTP server, please wait...");
        $process1 = new Process('cd ' . SERVER_WORKDIR . '/server/sftp && docker-compose up -d');
        $process1->setTimeout(3600);
        $process2 = new Process("docker exec dp-sftp service fail2ban start");

        try {
            $process1->mustRun();
            $output->writeln("Starting Fail2Ban, please wait...");
            $process2->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
