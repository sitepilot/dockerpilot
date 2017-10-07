<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        $output->writeln("Generating users config file...");
        $apps = sp_get_apps();

        $apps = sp_get_apps();

        if(is_array($apps) && count($apps) > 0) {
          foreach($apps as $dir=>$app) {
            $env = sp_get_env($dir);
            $config = '';
            if(! empty($env['APP_NAME']) && ! empty($env['APP_SFTP_PASS'])) {
              $config .= $env['APP_NAME'].":".$env['APP_SFTP_PASS'].":1000:100\n";
            }
          }
          $writeFile = SERVER_WORKDIR.'/server/sftp/config/users.conf';
          if(file_exists($writeFile)) {
            unlink($writeFile);
          }
          file_put_contents($writeFile, $config);
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
        $output->writeln("Starting SFTP server, please wait...");
        $process = new Process('cd server/sftp && docker-compose up -d');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
