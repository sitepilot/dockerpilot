<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Philo\Blade\Blade;

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
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->createConfig($output)) {
            if($this->startServer($output)) {
                $output->writeln("<info>SFTP server started!</info>");
            }
        }
    }

    /**
     * Create config.
     *
     * @return bool
     */
    protected function createConfig($output)
    {
        $apps = sp_get_apps();
        $sftpApps = '';
        $config = '';
        $sftpAppVolumes = '';

        // Get user and group id of current user (serverpilot)
        $process = new Process('id -u');
        $uID  = trim($process->mustRun()->getOutput());
        $process = new Process('id -g');
        $gID = trim($process->mustRun()->getOutput());

        // Create users config
        $output->writeln("Generating users config file...");

        if(is_array($apps) && count($apps) > 0) {
          foreach($apps as $dir=>$app) {
            $env = sp_get_env($dir);
            if(! empty($env['APP_NAME']) && ! empty($env['APP_SFTP_PASS'])) {
              $config .= $env['APP_NAME'].":".$env['APP_SFTP_PASS'].":".$uID.":".$gID."\n";
              $sftpAppVolumes .= "        - ".$dir.(isset($env['APP_SFTP_DIR']) ? '/'.$env['APP_SFTP_DIR'] : '' ).":/home/".$env['APP_NAME']."/public\n";
            }
          }
          $writeFile = SERVER_WORKDIR.'/server/sftp/users.conf';
          if(file_exists($writeFile)) {
            unlink($writeFile);
          }
          file_put_contents($writeFile, $config);
        }

        // Create docker-compose file
        $output->writeln("Generating docker-compose file...");
        $filePath = SERVER_WORKDIR.'/server/sftp/config/docker-compose.blade.php';

        $bladeFolder = SERVER_WORKDIR.'/server/sftp/config';
        $cache = SERVER_WORKDIR . '/cache';
        $views = sp_path($bladeFolder);

        if(file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('docker-compose', ['sftpAppVolumes' => $sftpAppVolumes])->render();
            $destFile = sp_path(SERVER_WORKDIR.'/server/sftp/docker-compose.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }

        return true;
    }

    /**
     * Starts the SFTP server.
     *
     * @return bool
     */
    protected function startServer($output)
    {
        $sftpID = sp_get_container_id('sp-sftp');

        // Stop container if running
        if($sftpID) {
            $command = $this->getApplication()->find('sftp:stop');
            $command->run(new ArrayInput([]), $output);
        }
        
        $output->writeln("Starting SFTP server, please wait...");
        $process = new Process('cd server/sftp && docker-compose up -d');
        $process->setTimeout(3600);

        try {
            $process->mustRun();

            $output->writeln("Starting Fail2Ban, please wait...");
            $process = new Process("docker exec sp-sftp service fail2ban start");

            try {
              $process->mustRun();
              return true;
            }catch (ProcessFailedException $e) {
                $output->writeln("<error>".$e->getMessage()."</error>");
            }
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
