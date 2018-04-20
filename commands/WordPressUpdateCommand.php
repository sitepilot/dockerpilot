<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WordPressUpdateCommand extends DockerpilotCommand
{
    private $codeBefore = '';
    private $restore = false;

    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('wp:update')
            ->setDescription('Update a WordPress application.')
            ->setHelp('This command updates a WordPress application.')
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
            $this->codeScreenshotBefore($output);
            $this->backupApp($output);
            $this->updateApp($output);
            $this->codeScreenshotAfter($output);
            $this->restoreApp($output);
            $output->writeln("<info>[" . $this->app . "] Update done!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>[" . $this->app . "] Update failed: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'Which app would you like to update?', 'running', 'apps/wordpress');
    }

    /**
     * Create code screenshot before update.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function codeScreenshotBefore(OutputInterface $output)
    {
        $domains = $this->appConfig['network']['domains'];
        $domains = explode(',', $domains);
        $this->domain = reset($domains);

        $output->writeln("Creating code screenshot of: " . $this->domain);

        $this->codeBefore = dp_get_code($this->domain);

        if (empty($this->codeBefore)) {
            throw new Exception('Could not get code screenshot before update from ' . $this->domain . '.');
        }
    }

    /**
     * Backup application.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function backupApp(OutputInterface $output)
    {
        $command = $this->getApplication()->find('app:backup');
        $arguments = array(
            '--app' => $this->app
        );
        $commandInput = new ArrayInput($arguments);
        $returnCode = $command->run($commandInput, $output);

        if(! $returnCode) {
            throw new Exception("Updated aborted, backup failed.");
        }
    }

    /**
     * Generate application files based on template.
     *
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function updateApp(OutputInterface $output)
    {
        $server = dp_get_config('server');
        $apps = dp_get_config('apps');

        $output->writeln("[" . $this->appConfig['name'] . "] Updating application...");
        $appDataDir = $apps['storagePath'] . '/' . $this->app . '/data';

        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/updateWP.yml --extra-vars "becomeUser=' . $server['user'] . ' app=' . $this->appConfig['name'] . ' host=' . $this->appConfig['host'] . ' appDataDir=' . $appDataDir . ' time=' . time() . '"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                $this->restore = true;
                $output->writeln($e->getMessage());
            }
        } else {
            throw new Exception("[" . $this->appConfig['name'] . "] Please enable Ansible in config.yml to use the update functionality!");
        }
    }

    /**
     * Create code screenshot after update.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function codeScreenshotAfter(OutputInterface $output)
    {
        $output->writeln("Creating code screenshot of: " . $this->domain);

        $codeAfter = dp_get_code($this->domain);

        if (empty($this->codeBefore)) {
            $this->restore = true;
            $output->writeln('<error>Could not get code screenshot after update from ' . $this->domain . '.</error>');
        } else {
            similar_text($this->codeBefore, $codeAfter, $percent);
            if ($percent > 96) {
                $output->writeln("<info>Code compare OK</info>");
            } else {
                $output->writeln("<error>Code compare NOT OK</error>");
                $this->restore = true;
            }
        }
    }

    /**
     * Backup application.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function restoreApp(OutputInterface $output)
    {
        if ($this->restore = true) {
            $command = $this->getApplication()->find('app:restore');
            $arguments = array(
                '--app' => $this->app
            );

            $commandInput = new ArrayInput($arguments);
            $returnCode = $command->run($commandInput, $output);

            if(! $returnCode) {
                throw new Exception("Restore failed, inform an admin!");
            }
        }
    }
}