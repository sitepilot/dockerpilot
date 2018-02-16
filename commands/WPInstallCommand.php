<?php

namespace Serverpilot\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WPInstallCommand extends ServerpilotCommand
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
            ->addOption('appName', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->userInput($input, $output)) {
            if ($this->installWP($output)) {
                $output->writeln('<info>WordPress installed!</info>');
            }
        }
    }

    /**
     * Ask user for name and app.
     *
     * @return bool
     */
    protected function userInput($input, $output)
    {
        return $this->askForApp($input, $output, 'In which app would you like to install WordPress?', 'running');
    }

    /**
     * Install WordPress in app directory.
     *
     * @return bool
     */
    protected function installWP($output)
    {
        $env = sp_get_env($this->appDir);
        $wpConfigFile = $this->appDir . '/app/wp-config.php';

        if (!file_exists($wpConfigFile)) {
            if (!empty($env['APP_DB_DATABASE']) && !empty($env['APP_DB_USER']) && !empty($env['APP_DB_HOST']) && !empty($env['APP_DB_USER_PASSWORD'])) {

                $dbName = $env['APP_DB_DATABASE'];
                $dbHost = $env['APP_DB_HOST'];
                $dbUser = $env['APP_DB_USER'];
                $dbPass = $env['APP_DB_USER_PASSWORD'];
                $container = 'sp-app-' . $env['APP_NAME'];
                $containerID = sp_get_container_id($container);

                if ($containerID) {
                    $command1 = "docker exec --user serverpilot $container wp core download --path=/var/www/html";
                    $command2 = 'docker exec --user serverpilot ' . $container . ' wp config create --path=/var/www/html --skip-check --dbname=' . $dbName . ' --dbhost=' . $dbHost . ' --dbuser=' . $dbUser . ' --dbpass=' . $dbPass . ' --dbprefix=sp_';

                    $process1 = new Process($command1);
                    $process2 = new Process($command2);

                    try {
                        $output->writeln('Downloading WordPress core...');
                        $process1->mustRun();

                        $output->writeln('Creating configuration...');
                        $process2->mustRun();

                        sp_change_env_var($this->appDir, 'APP_TEMPLATE', 'wordpress');

                        return true;
                    } catch (ProcessFailedException $e) {
                        $output->writeln("<error>" . $e->getMessage() . "</error>");
                    }
                } else {
                    $output->writeln("<error>Can't find application container ID.</error>");
                }
            }
        } else {
            $output->writeln("<error>WordPress is already installed in app: $this->appName.</error>");
        }
        return false;
    }
}
