<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WPInstallCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('wp:install')
            ->setDescription('Install WordPress in an app.')
            ->setHelp('This command installs WordPress in an app.')
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
            $this->installWP($output);
            $output->writeln('<info>WordPress installed!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to install WordPress: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'In which app would you like to install WordPress?', 'running');
    }

    /**
     * Install WordPress in app directory.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function installWP(OutputInterface $output)
    {
        $env = dp_get_env($this->appDir);
        $wpConfigFile = $this->appDir . '/app/wp-config.php';

        if (!file_exists($wpConfigFile)) {
            if (!empty($env['APP_DB_DATABASE']) && !empty($env['APP_DB_USER']) && !empty($env['APP_DB_HOST']) && !empty($env['APP_DB_USER_PASSWORD'])) {

                $dbName = $env['APP_DB_DATABASE'];
                $dbHost = $env['APP_DB_HOST'];
                $dbUser = $env['APP_DB_USER'];
                $dbPass = $env['APP_DB_USER_PASSWORD'];
                $container = 'dp-app-' . $env['APP_NAME'];
                $containerID = dp_get_container_id($container);

                if ($containerID) {
                    $command1 = "docker exec --user " . SERVER_USER . " $container wp core download --path=/var/www/html";
                    $command2 = 'docker exec --user ' . SERVER_USER . ' ' . $container . ' wp config create --path=/var/www/html --skip-check --dbname=' . $dbName . ' --dbhost=' . $dbHost . ' --dbuser=' . $dbUser . ' --dbpass=' . $dbPass . ' --dbprefix=sp_';

                    $process1 = new Process($command1);
                    $process2 = new Process($command2);

                    try {
                        $output->writeln('Downloading WordPress core...');
                        $process1->mustRun();

                        $output->writeln('Creating configuration...');
                        $process2->mustRun();

                        dp_change_env_var($this->appDir, 'APP_TEMPLATE', 'wordpress');
                    } catch (ProcessFailedException $e) {
                        throw new Exception($e->getMessage());
                    }
                } else {
                    throw new Exception("Can't find application container ID.");
                }
            }
        } else {
            throw new Exception("WordPress is already installed in app: $this->app.");
        }
    }
}
