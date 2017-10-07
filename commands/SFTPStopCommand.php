<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SFTPStopCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('sftp:stop')
             ->setDescription('Stops the SFTP server.')
             ->setHelp('This command stops the SFTP server.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->stopServer($output)) {
          $output->writeln("<info>SFTP server stopped!</info>");
        }
    }

    /**
     * Stops the SFTP server.
     *
     * @return bool
     */
    protected function stopServer($output)
    {
        $output->writeln("Stopping SFTP server, please wait...");
        $process = new Process('cd tools/sftp && docker-compose down && docker-compose rm');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }
}
