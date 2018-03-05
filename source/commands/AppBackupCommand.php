<?php

namespace Dockerpilot\Command;

use Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
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
     * Backup directory.
     *
     * @var string
     */
    protected $backupDir = '';

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

        try {
            $this->userInput($input, $output);
            $this->backupFiles($output);
            $this->backupDatabase($output);
            $this->cleanupFiles($output);
            $output->writeln('<info>Backup done!</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Backup failed: ' . $e->getMessage() . '</error>');
        }
    }

    /**
     * Ask which application we need to backup.
     *
     * @param $input
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to backup?', false);
        $this->backupDir = "/dockerpilot/backups/" . $this->app;
    }

    /**
     * Backup application files.
     *
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function backupFiles(OutputInterface $output)
    {
        $output->writeln('Backup application files...');

        $backupFile = (SERVER_BACKUP_TIMESTAMP ? $this->app . '_' . $this->fileDate . '.tar.gz' : $this->app . '.tar.gz');
        $backupAppDir = "/dockerpilot/apps/" . $this->app;

        $command = "docker exec --user=" . SERVER_USER . " dp-mysql bash -c \"mkdir -p $this->backupDir && cd " . $backupAppDir . " && tar --warning=none -h -pczf " . $this->backupDir . "/" . $backupFile . " *\"";
        $process = new Process($command);

        try {
            $process->setTimeout(3600);
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Backup application database.
     *
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function backupDatabase(OutputInterface $output)
    {
        $output->writeln('Backup application database...');

        $backupFile = (SERVER_BACKUP_TIMESTAMP ? $this->app . '_' . $this->fileDate . '.sql' : $this->app . '.sql');
        $env = dp_get_env($this->appDir);

        if (isset($env['APP_DB_DATABASE'])) {
            $dbName = $env['APP_DB_DATABASE'];
            $command = "docker exec dp-mysql bash -c \"MYSQL_PWD=" . MYSQL_ROOT_PASSWORD . " mysqldump $dbName > " . $this->backupDir . "/" . $backupFile . " && chown -R " . SERVER_USER . ":" . SERVER_USER . " " . $this->backupDir . "/* \"";
            $process = new Process($command);

            try {
                $process->setTimeout(3600);
                $process->mustRun();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Cleanup old backup files.
     *
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function cleanupFiles(OutputInterface $output)
    {
        $output->writeln('Cleanup old backup files...');

        $localBackupDir = SERVER_BACKUP_DIR . '/' . $this->app;
        $adapter = new Local($localBackupDir);
        $filesystem = new Filesystem($adapter);
        $content = $filesystem->listContents();

        foreach ($content as $file) {
            if ($file['timestamp'] < (time() - SERVER_BACKUP_KEEP_DAYS * 86400)) {
                try {
                    $filesystem->delete($file['path']);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        }
    }
}
