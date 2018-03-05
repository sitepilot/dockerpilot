<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AppCmdCommand extends DockerpilotCommand
{
    /**
     * The command to run in the application.
     *
     * @var string
     */
    protected $command = '';

    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:cmd')
            ->setDescription('Run a command inside an application container.')
            ->setHelp('Run a command inside an application container.')
            ->addOption('app', null, InputOption::VALUE_OPTIONAL)
            ->addOption('command', null, InputOption::VALUE_OPTIONAL);
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
            $this->runCommand($output);
        } catch (Exception $e) {
            $output->writeln("<error>Failed to run command: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');
        $this->askForApp($input, $output, 'In which app would you like to run your command?', 'started');

        if (!$input->getOption('command')) {
            $question = new Question('Command: ');
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException(
                        "Sorry, i can't run an empty command."
                    );
                }
                return $answer;
            });
            $this->command = trim($questionHelper->ask($input, $output, $question));
        } else {
            $this->command = trim($input->getOption('command'));
        }
    }

    /**
     * Execute command in the application container.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function runCommand(OutputInterface $output)
    {
        $container = 'dp-app-' . $this->app;
        $command = "docker exec --user " . SERVER_USER . " -it $container bash -c \"" . $this->command . "\"";

        if (!dp_is_windows()) {
            $process = new Process($command);
            try {
                $process->setTty(true);
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            $output->writeln("<info>Dockerpilot can't run custom commands inside application containers for you on Windows.</info>");
            $output->writeln("<info>Copy and paste the following command to execute your command:</info>\n");
            $output->writeln($command);
        }
    }
}
