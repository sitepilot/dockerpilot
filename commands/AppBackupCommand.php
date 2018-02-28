<?php

namespace Dockerpilot\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppBackupCommand extends DockerpilotCommand
{
    /**
     * Save current date and time before starting backup.
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fileDate = date('Ymd_His');
        if ($this->userInput($input, $output)) {
            if ($this->backupFiles($output)) {
                if ($this->backupDatabase($output)) {
                    $output->writeln('<info>Backup ready!</info>');
                }
            }
        }
    }

    /**
     * Ask which app Dockerpilot needs to backup.
     *
     * @param $input
     * @param $output
     * @return array
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        return $this->askForApp($input, $output, 'Which app would you like to backup?', false);
    }

    /**
     * Backup application files.
     *
     * @param $output
     * @return bool
     */
    protected function backupFiles(OutputInterface $output)
    {
        $output->writeln('Backup application files...');

        $backupFile = (SERVER_BACKUP_TIMESTAMP ? $this->app . '_' . $this->fileDate . '.tar.gz' : $this->app . '.tar.gz');
        $backupDir = "/dockerpilot/backups/" . $this->app;
        $backupAppDir = "/dockerpilot/apps/" . $this->app;

        $command = "docker exec --user=dockerpilot dp-mysql bash -c \"mkdir -p $backupDir && cd " . $backupAppDir . " && tar --warning=none -h -pczf " . $backupDir . "/" . $backupFile . " *\"";
        $process = new Process($command);

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
        }
        return false;
    }

    /**
     * Backup application database.
     *
     * @param $output
     * @return bool
     */
    protected function backupDatabase(OutputInterface $output)
    {
        $output->writeln('Backup application database...');

        $backupFile = (SERVER_BACKUP_TIMESTAMP ? $this->app . '_' . $this->fileDate . '.sql' : $this->app . '.sql');
        $backupDir = "/dockerpilot/backups/" . $this->app;
        $env = sp_get_env($this->appDir);

        if (isset($env['APP_DB_DATABASE'])) {

            $dbName = $env['APP_DB_DATABASE'];
            $command = "docker exec dp-mysql bash -c \"MYSQL_PWD=" . MYSQL_ROOT_PASSWORD . " mysqldump $dbName > " . $backupDir . "/" . $backupFile . " && chown -R dockerpilot:dockerpilot " . $backupDir . "/* \"";
            $process = new Process($command);

            try {
                $process->mustRun();
                return true;
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>" . $e->getMessage() . "</error>");
            }
        }
        return false;
    }

}
