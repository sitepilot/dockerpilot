<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppDeleteCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:delete')
            ->setDescription('Delete an application.')
            ->setHelp('This command deletes an application.')
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
        try {
            $this->userInput($input, $output);
            $this->deleteApp($output);
            $output->writeln('<info>App deleted!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to delete application: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user which app needs to be deleted.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to delete?', 'stopped');
    }

    /**
     * Delete the application.
     *
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function deleteApp(OutputInterface $output)
    {
        $dbContainer = dp_get_container_id('dp-mysql');

        if ($dbContainer) {
            // Read environment file
            $env = dp_get_env($this->appDir);

            // Check if we need to delete a database table and user
            if (!empty($env['APP_DB_USER']) && !empty($env['APP_DB_DATABASE'])) {
                // Remove database table and user (if exists)
                $output->writeln('Removing database table and user...');

                $dbName = $env['APP_DB_DATABASE'];
                $dbUser = $env['APP_DB_USER'];

                $command = "docker exec dp-mysql bash -c \"MYSQL_PWD=" . MYSQL_ROOT_PASSWORD . " mysql -u root -e " . '\"' . "DROP DATABASE IF EXISTS $dbName; GRANT USAGE ON *.* TO '$dbUser'@'%' IDENTIFIED BY 'dummypass'; DROP USER '$dbUser'@'%';" . '\"' . "\"";
                $process = new Process($command);
                try {
                    $process->mustRun();
                    dp_rmdir($this->appDir);
                } catch (ProcessFailedException $e) {
                    throw new Exception($e->getMessage());
                }
            }
        } else {
            throw new Exception("Can't connect to database. Please start the server with `dp server:start`.");
        }
    }
}
