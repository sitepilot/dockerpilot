<?php

namespace Dockerpilot\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
            ->addOption('appName', null, InputOption::VALUE_OPTIONAL)
            ->addOption('command', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->userInput($input, $output)) {
            if ($this->runCommand($input, $output)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ask user for application.
     *
     * @param $input
     * @param $output
     * @return bool
     */
    protected function userInput($input, $output)
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

        return true;
    }

    /**
     * Execute command in the application container.
     *
     * @param $input
     * @param $output
     * @return bool
     */
    protected function runCommand($input, $output)
    {
        $container = 'dp-app-' . $this->appName;
        $command = "docker exec --user dockerpilot -it $container bash -c \"" . $this->command . "\"";

        if (!sp_is_windows()) {
            $process = new Process($command);
            try {
                $process->setTty(true);
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>" . $e->getMessage() . "</error>");
            }
        } else {
            $output->writeln("<info>Dockerpilot can't run custom commands inside application containers for you on Windows.</info>");
            $output->writeln("<info>Copy and paste the following command to execute your command:</info>\n");
            $output->writeln($command);
        }

        return true;
    }
}
