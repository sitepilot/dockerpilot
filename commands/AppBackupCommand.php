<?php
namespace Dockerpilot\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AppBackupCommand extends DockerpilotCommand
{
    /**
     * Save current date and time before backup.
     *
     * @var string
     */
    protected $fileDate = '';

    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:backup')
             ->setDescription('Backup an application.')
             ->setHelp('This command backups an application.')
             ->addOption('app', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fileDate = date('Y-m-d_H:i:s');
        if($this->userInput($input, $output)) {
            if($this->backupFiles($output)) {
                if($this->backupDatabase($output)) {
                    $output->writeln('<info>Backup ready!</info>');
                }
            }
        }
    }

    /**
     * Ask which app needs a backup.
     *
     * @return bool
     */
    protected function userInput($input, $output)
    {
        return $this->askForApp($input, $output, 'Which app would you like to backup?', false);
    }

    /**
     * Backup app files.
     *
     * @return bool
     */
    protected function backupFiles($output)
    {
      $output->writeln('Backing up application files...');
      $backupFileName = (SERVER_BACKUP_TIMESTAMP ? $this->app.'_'.$this->fileDate.'.zip' : $this->app.'.zip');
      $backupFile = SERVER_BACKUP_DIR."/".$backupFileName;
      $command = "cd ".$this->appDir."; zip -r ".$backupFile." .";
      $process = new Process($command);

      if(file_exists($backupFile)) {
        $output->writeln("Removing old backup...");
        unlink($backupFile);
      }

      try {
          $process->mustRun();

          return true;
      } catch (ProcessFailedException $e) {
          $output->writeln("<error>".$e->getMessage()."</error>");
      }

      return false;
    }

    /**
     * Backup app db.
     *
     * @return bool
     */
    protected function backupDatabase($output)
    {
      $output->writeln('Backing up application database...');
      $backupFileName = (SERVER_BACKUP_TIMESTAMP ? $this->app.'_'.$this->fileDate.'.sql' : $this->app.'.sql');
      $backupFile = SERVER_BACKUP_DIR."/".$backupFileName;
      $env = sp_get_env($this->appDir);

      if(isset($env['APP_DB_DATABASE'])) {

        if(file_exists($backupFile)) {
          $output->writeln("Removing old backup...");
          unlink($backupFile);
        }

        $dbName = $env['APP_DB_DATABASE'];
        $command = "docker exec dp-mysql bash -c \"MYSQL_PWD=".MYSQL_ROOT_PASSWORD." mysqldump $dbName > /dockerpilot/backup/".$backupFileName ." && chown dockerpilot:dockerpilot /dockerpilot/backup/".$backupFileName ."\"";
        $process = new Process($command);

        try {
            $process->mustRun();

            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
      }
    }

}
