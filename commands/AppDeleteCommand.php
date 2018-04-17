<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use UptimeRobot\API;

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
            ->setHelp('This command deletes an app.')
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
            $this->deleteMonitor($output);
            $this->deleteApp($input, $output);
            $output->writeln('<info>App deleted!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to delete application: \n" . $e->getMessage() . "</error>");
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
        $this->askForApp($input, $output, 'Which app would you like to delete?', 'stopped');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Are you sure you want to remove ' . $this->app . '? ',
            false,
            '/^(y|j)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            throw new Exception('Application not deleted, confirmation failed.');
        }
    }

    /**
     * Delete app monitor in UptimeRobot.
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function deleteMonitor(OutputInterface $output)
    {
        $uptimeRobot = dp_get_config('uptimeRobot');
        $app = dp_get_app_config($this->appDir);

        if(!empty($uptimeRobot['apiKey']) && !empty($app['monitor']['domain'])) {
            $output->writeln('Deleting monitor...');
            try {
                // Check if monitor exists
                $config = [
                    'apiKey' => $uptimeRobot['apiKey'],
                    'url' => 'https://api.uptimerobot.com'
                ];
                $api = new API($config);
                $result = $api->request('/getMonitors');
                if(!empty($result['stat']) && $result['stat'] == 'fail') {
                    $output->writeln('<error>Deleting monitor failed: ' . $result['message'] . '.</error>');
                } else {
                    $found = false;
                    foreach ($result['monitors']['monitor'] as $monitor) {
                        if ($monitor['friendlyname'] == $app['name']) {
                            $found = $monitor['id'];
                        }
                    }

                    if($found) {
                        $output->writeln('Monitor found, deleting monitor...');
                        $args = [
                            'monitorID' => $found,
                        ];

                        $result = $api->request('/deleteMonitor', $args);
                        if (!empty($result['stat']) && $result['stat'] == 'fail') {
                            $output->writeln('<error>Deleting monitor failed: ' . $result['message'] . '.</error>');
                        }
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Delete the app.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function deleteApp(InputInterface $input, OutputInterface $output)
    {
        $app = dp_get_app_config($this->appDir);
        $server = dp_get_config('server');
        $apps = dp_get_config('apps');

        $output->writeln("Deleting application storage dir on host...");
        $appStorageDir = $apps['storagePath'] . '/' . $this->app;
        if ($server['useAnsible'] == 'true') {
            $process = new Process('ansible-playbook ' . SERVER_WORKDIR . '/playbooks/deleteAppDir.yml --extra-vars "host=' . $app['host'] . ' app_dir=' . $appStorageDir . '"');
            $process->setTimeout(3600);

            try {
                $process->mustRun();
                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            if (file_exists($appStorageDir)) {
                try {
                    dp_delete_dir(dp_path($appStorageDir));
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        }

        $output->writeln("Removing application config dir...");
        $appConfigDir = $apps['configPath'] . '/' . $this->app;

        if (file_exists($appConfigDir)) {
            try {
                dp_delete_dir(dp_path($appConfigDir));
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}