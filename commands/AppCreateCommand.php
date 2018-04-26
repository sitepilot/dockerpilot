<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AppCreateCommand extends DockerpilotCommand
{
    /**
     * The application name.
     *
     * @var string
     */
    protected $app = [];

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
            ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'The application user.')
            ->addOption('node', null, InputOption::VALUE_OPTIONAL, 'The application node.')
            ->addOption('dbHost', null, InputOption::VALUE_OPTIONAL, 'The application database host.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getDockerNodes();

        try {
            $this->userInput($input, $output);
            $this->createConfigDir($input, $output);
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
     * @throws Exception
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
            $this->app['name'] = trim($questionHelper->ask($input, $output, $question));
        } else {
            $this->app['name'] = trim($input->getOption('app'));
        }

        if (!$input->getOption('user')) {
            $question = new Question('Application user? ');
            $question->setValidator(function ($answer) {
                if (empty($answer) && strlen($answer) < 3) {
                    throw new \RuntimeException(
                        'The user of the application should be at least 3 characters long.'
                    );
                }
                return $answer;
            });
            $this->app['user'] = trim($questionHelper->ask($input, $output, $question));
        } else {
            $this->app['user'] = trim($input->getOption('user'));
        }

        $nodes = $this->getDockerNodes();
        if (!$input->getOption('node')) {
            $question = new ChoiceQuestion(
                'Please select a node:',
                $nodes, 0
            );
            $question->setErrorMessage('Node %s is invalid.');
            $this->app['host'] = $questionHelper->ask($input, $output, $question);
        } else {
            if (in_array($input->getOption('node'), $nodes)) {
                $this->app['host'] = trim($input->getOption('node'));
            }
        }

        $mysql = dp_get_config('mysql');
        $dbServers = [];
        foreach($mysql['servers'] as $dbServer) {
            $dbServers[] = $dbServer['name'];
        }
        if (!$input->getOption('dbHost')) {
            $question = new ChoiceQuestion(
                'Please select a database host:',
                $dbServers, 0
            );
            $question->setErrorMessage('Host %s is invalid.');
            $this->app['database']['host'] = $questionHelper->ask($input, $output, $question);
        } else {
            if (in_array($input->getOption('dbHost'), $nodes)) {
                $this->app['database']['host'] = trim($input->getOption('dbHost'));
            }
        }

        $this->app['name'] = dp_create_slug($this->app['name']);
        $this->app['database']['name'] = $this->app['name'];
        $this->app['database']['user'] = $this->app['name'] . '_' . dp_random_password(6);
        $this->app['database']['password'] = dp_random_password(14);
    }

    /**
     * Create the application directory from a template.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function createConfigDir(InputInterface $input, OutputInterface $output)
    {
        $server = dp_get_config('server');
        $appDir = $server['storagePath'] . '/config/apps/' . $this->app['name'];

        #toDo create database
        $output->writeln("Creating application directory...");

        if (file_exists($appDir)) {
            throw new Exception('Application already exists, please choose another name.');
        } else {
            mkdir($appDir, 0750, true);
        }

        $filePath = SERVER_WORKDIR . '/stacks/app/config.blade.php';
        $bladeFolder = SERVER_WORKDIR . '/stacks/app';
        $cache = SERVER_WORKDIR . '/cache';
        $views = dp_path($bladeFolder);

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('config', ['app' => $this->app, 'server' => $server])->render();
            $destFile = dp_path($appDir . '/config.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);

            $output->writeln("<info>Application created!</info>\n");

            $output->writeln("--------------");
            $output->writeln('App Name: ' . $this->app['name']);
            $output->writeln('App Host: ' . $this->app['host']);
            $output->writeln('Database Host: ' . $this->app['database']['host']);
            $output->writeln('Database Name: ' . $this->app['database']['name']);
            $output->writeln('Database User: ' . $this->app['database']['user']);
            $output->writeln('Database Password: ' . $this->app['database']['password']);
            $output->writeln('--------------');

            $output->write("\n<info>Don't forget to create the database before starting the application.</info>");
        }
    }
}