<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppCreateCommand extends Command
{
    /**
     * The application name.
     *
     * @var string
     */
    protected $app = '';

    /**
     * The application template.
     *
     * @var string
     */
    protected $appStack = '';

    /**
     * The application domains.
     *
     * @var string
     */
    protected $appDomains = '';

    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:create')
            ->setDescription('Create an application.')
            ->setHelp('This command creates an application.')
            ->addOption('app', null, InputOption::VALUE_OPTIONAL, 'The application name.')
            ->addOption('stack', null, InputOption::VALUE_OPTIONAL, 'The application stack (e.g. lamp).')
            ->addOption('config', null, InputOption::VALUE_OPTIONAL,
                'Set software dependent configuration (currently available: laravel).')
            ->addOption('domains', null, InputOption::VALUE_OPTIONAL, 'The application domains (comma separated).');
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
            $this->createAppDir($input, $output);
        } catch (Exception $e) {
            $output->writeln("<error>Failed to create the application: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for name and stack.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');

        if (!$input->getOption('app')) {
            $question = new Question('Application name? ');
            $question->setValidator(function ($answer) {
                if (empty($answer) && strlen($answer) < 3) {
                    throw new \RuntimeException(
                        'The name of the application should be at least 3 characters long.'
                    );
                }
                return $answer;
            });
            $this->app = trim($questionHelper->ask($input, $output, $question));
        } else {
            $this->app = trim($input->getOption('app'));
        }

        if (!$input->getOption('domains')) {
            $question = new Question('Application domain (without http:// or https://)? ');
            $question->setValidator(function ($answer) {
                if (empty($answer) || false === filter_var('http://' . $answer, FILTER_VALIDATE_URL)) {
                    throw new \RuntimeException(
                        'Please enter a valid domain.'
                    );
                }
                return $answer;
            });
            $this->appDomains = trim($questionHelper->ask($input, $output, $question));
        } else {
            $this->appDomains = trim($input->getOption('domains'));
        }

        $templates = array_values(dp_get_stacks());
        if (!$input->getOption('stack')) {
            $question = new ChoiceQuestion(
                'Please select a stack:',
                $templates, 0
            );
            $question->setErrorMessage('Stack %s is invalid.');
            $this->appStack = $questionHelper->ask($input, $output, $question);
        } else {
            if (in_array($input->getOption('stack'), $templates)) {
                $this->appStack = trim($input->getOption('stack'));
            }
        }
    }

    /**
     * Create the application directory from a template.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function createAppDir(InputInterface $input, OutputInterface $output)
    {
        $dbContainer = dp_get_container_id('dp-mysql');

        if ($dbContainer) {
            $output->writeln("Creating application directory...");

            if ($this->app && $this->appStack) {
                $appSlug = dp_create_slug($this->app);
                $appDir = SERVER_APP_DIR . '/' . $appSlug;
                $stackDir = SERVER_STACK_DIR . '/' . $this->appStack . '/1.0';

                $dbPassword = dp_random_password(16);
                $dbUser = $appSlug;
                $dbName = $appSlug . '_' . dp_random_password(6);

                $command = "docker exec dp-mysql bash -c \"MYSQL_PWD=" . MYSQL_ROOT_PASSWORD . " mysql -u root -e " . '\"' . "CREATE DATABASE IF NOT EXISTS $dbName; CREATE USER '$dbUser'@'%' IDENTIFIED BY '$dbPassword'; GRANT ALL ON $dbName.* TO '$dbUser'@'%';" . '\"' . "\"";
                $process = new Process($command);

                if (!file_exists($appDir)) {
                    try {
                        // Create application database
                        $output->writeln("Creating database...");
                        $process->mustRun();

                        // Copy application directory
                        dp_copy_directory($stackDir, $appDir);

                        // Update application environment file
                        $sftpPass = crypt(md5(uniqid()), 'dockerpilot');
                        dp_change_env_var($appDir, 'APP_NAME', $appSlug);
                        dp_change_env_var($appDir, 'APP_DOMAINS', $this->appDomains);
                        dp_change_env_var($appDir, 'APP_SFTP_PASS', $sftpPass);
                        dp_change_env_var($appDir, 'APP_DB_USER', $dbUser);
                        dp_change_env_var($appDir, 'APP_DB_DATABASE', $dbName);
                        dp_change_env_var($appDir, 'APP_DB_USER_PASSWORD', $dbPassword);

                        if ($input->getOption('config') == 'laravel') {
                            dp_change_env_var($appDir, 'APP_MOUNT_POINT', '/var/www');
                            dp_change_env_var($appDir, 'APP_VOLUME_1', './app/public:/var/www/html');
                        }

                        // Inform the user about created application
                        $output->writeln('<info>Application created!</info>');

                        $output->writeln("\n--------------");
                        $output->writeln('App Name: ' . $appSlug);
                        $output->writeln('App Domains: ' . $this->appDomains);
                        $output->writeln('Database Host: dp-mysql');
                        $output->writeln('Database Name: ' . $dbName);
                        $output->writeln('Database User: ' . $dbUser);
                        $output->writeln('Database Password: ' . $dbPassword);
                        $output->writeln('SFTP Password: ' . $sftpPass);
                        $output->writeln('--------------');
                    } catch (ProcessFailedException $e) {
                        throw new Exception($e->getMessage());
                    }
                } else {
                    throw new Exception("Application already exists.");
                }
            }
        } else {
            throw new Exception("Can't create application database. Please start the server with `dp server:start`.");
        }
    }
}
