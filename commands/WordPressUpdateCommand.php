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
            ->addOption('app', null, InputOption::VALUE_OPTIONAL)
            ->addOption('all', null, InputOption::VALUE_OPTIONAL);
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
        if($input->getOption('all') == 'true') {
            $apps = $this->getApps('running', 'apps/wordpress');
            $slackMessages = [];
            foreach ($apps as $app) {
                $command = $this->getApplication()->find('wp:update');
                $arguments = array(
                    '--app' => $app
                );
                $commandInput = new ArrayInput($arguments);
                $returnCode = $command->run($commandInput, $output);

                if ($returnCode) {
                    $slackMessages[] = [
                        "text" => "Update of " . $app . " succeeded.\n",
                        "params" => [
                            "color" => "#2ecc71"
                        ]
                    ];
                } else {
                    $slackMessages[] = [
                        "text" => "Update of " . $app . " failed.\n",
                        "params" => [
                            "color" => "#e74c3c"
                        ]
                    ];
                }
            }
            $this->slackSendAttachment(
                "Autopilot Report " . date('Y-m-d H:i:s'),
                $slackMessages
            );
        } else {
            try {
                $this->userInput($input, $output);
                $this->codeScreenshotBefore($output);
                $this->backupApp($output);
                $this->updateApp($output);
                $this->codeScreenshotAfter($output);
                $this->restoreApp($output);
                $this->notify($output, "Update done!");
                if(! $this->restore) {
                    return 1;
                }
                $this->restore = false;
                return 0;
            } catch (Exception $e) {
                $this->notify($output, "Dockerpilot Update", $e, true, '#e74c3c');
                return 0;
            }
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
        $this->domain = 'http://' . reset($domains);

        $this->notify($output, "Creating code screenshot of: " . $this->domain . ".");
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

        if (!$returnCode) {
            throw new Exception("Update aborted, backup failed.");
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

        $this->notify($output, "Updating application.");
        $appDataDir = $apps['storagePath'] . '/' . $this->app . '/data';

        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/updateWP.yml --extra-vars "becomeUser=' . $server['user'] . ' app=' . $this->appConfig['name'] . ' host=' . $this->appConfig['host'] . ' appDataDir=' . $appDataDir . ' time=' . time() . '"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                $this->notify($output, "Dockerpilot Update", $process->getOutput());
            } catch (ProcessFailedException $e) {
                $this->restore = true;
                $this->notify($output, "Dockerpilot Update", $e, true, '#e74c3c');
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
        $this->notify($output, "Creating code screenshot of: " . $this->domain . ".");
        $codeAfter = dp_get_code($this->domain);

        similar_text($this->codeBefore, $codeAfter, $percent);
        if ($percent > 96) {
            $this->notify($output, "Code compare OK, compare result: " . $percent . "%.");
        } else {
            $this->notify($output, "Dockerpilot Update",
                'Code compare after update failed, compare result: ' . $percent . '%.', true, '#e74c3c');
            $this->restore = true;
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
        if ($this->restore == true) {
            $command = $this->getApplication()->find('app:restore');
            $arguments = array(
                '--app' => $this->app
            );

            $this->notify($output, "Dockerpilot Update", "Restoring last backup.", true, "#f1c40f");

            $commandInput = new ArrayInput($arguments);
            $returnCode = $command->run($commandInput, $output);

            if (!$returnCode) {
                throw new Exception("Restore failed, inform an admin!");
            }
        }
    }
}