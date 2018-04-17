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
            ->addOption('stack', null, InputOption::VALUE_OPTIONAL, 'The application stack.')
            ->addOption('node', null, InputOption::VALUE_OPTIONAL, 'The application node.')
            ->addOption('dbHost', null, InputOption::VALUE_OPTIONAL, 'The application database host.')
            ->addOption('adminUser', null, InputOption::VALUE_OPTIONAL, 'The application admin username.')
            ->addOption('adminEmail', null, InputOption::VALUE_OPTIONAL, 'The application admin email.');
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

        $stacks = dp_get_app_stacks();
        if (!$input->getOption('stack')) {
            $question = new ChoiceQuestion(
                'Please select a stack:',
                $stacks, 0
            );
            $question->setErrorMessage('Stack %s is invalid.');
            $this->app['stack'] = 'apps/' . $questionHelper->ask($input, $output, $question);
        } else {
            if (in_array($input->getOption('stack'), $stacks)) {
                $this->app['stack'] = trim($input->getOption('stack'));
            }
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

        if($this->app['stack'] == 'apps/wordpress') {
            if (!$input->getOption('dbHost')) {
                $question = new Question('Database hostname? ');
                $this->app['database']['host'] = trim($questionHelper->ask($input, $output, $question));
            } else {
                $this->app['database']['host'] = trim($input->getOption('dbHost'));
            }

            if (!$input->getOption('adminUser')) {
                $question = new Question('Admin username? ');
                $this->app['admin']['user'] = trim($questionHelper->ask($input, $output, $question));
            } else {
                $this->app['admin']['user'] = trim($input->getOption('adminUser'));
            }

            if (!$input->getOption('adminEmail')) {
                $question = new Question('Admin email? ');
                $this->app['admin']['email'] = trim($questionHelper->ask($input, $output, $question));
            } else {
                $this->app['admin']['email'] = trim($input->getOption('adminEmail'));
            }

            $this->app['database']['password'] = dp_random_password();
        }

        $this->app['name'] = dp_create_slug($this->app['name']);
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
        $dbService = $this->getDockerServiceID('mysql_db');
        $apps = dp_get_config('apps');

        if ($dbService) {
            #toDo create database
            $output->writeln("Creating application directory...");

            $appDir = $apps['configPath'] . '/' . $this->app['name'];
            $stackDir = SERVER_WORKDIR . '/stacks/' . $this->app['stack'];

            if (file_exists($appDir)) {
                throw new Exception('Application already exists, please choose another name.');
            } else {
                mkdir($appDir, 0750, true);
            }

            $filePath = $stackDir . '/config.blade.php';
            $bladeFolder = $stackDir;
            $cache = SERVER_WORKDIR . '/data/cache';
            $views = dp_path($bladeFolder);
            $server = dp_get_config('server');

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

                if(! empty($this->app['database'])):
                $output->writeln('Database Host: ' . $this->app['database']['host']);
                $output->writeln('Database Name: ' . $this->app['name']);
                $output->writeln('Database User: ' . $this->app['name']);
                $output->writeln('Database Password: ' . $this->app['database']['password']);
                endif;

                if(! empty($this->app['admin'])):
                $output->writeln('Admin User: ' . $this->app['admin']['user']);
                $output->writeln('Admin Email: ' . $this->app['admin']['email']);
                endif;

                $output->writeln('--------------');

                if(! empty($this->app['database'])):
                    $output->write("\n<info>Don't forget to create the database before starting the application.</info>");
                endif;
            }
        } else {
            throw new Exception("Can't create application database. Please start the database server with `dp mysql:start`.");
        }
    }
}