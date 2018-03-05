<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WPUpdateCommand extends DockerpilotCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('wp:update')
            ->setDescription('Update WordPress in an app.')
            ->setHelp('This command updates WordPress in an app.')
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
            $this->updateWP($output);
            $output->writeln('<info>WordPress updated!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>Failed to update WordPress: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Ask user for the application.
     *
     * @param $input
     * @param $output
     * @return void
     * @throws Exception
     */
    protected function userInput(InputInterface $input, OutputInterface $output)
    {
        $this->askForApp($input, $output, 'In which app would you like to update WordPress?', 'running');
    }

    /**
     * Update WordPress in app directory.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function updateWP(OutputInterface $output)
    {
        $env = dp_get_env($this->appDir);
        $wpConfigFile = $this->appDir . '/app/wp-config.php';

        if (file_exists($wpConfigFile) && $env['APP_TEMPLATE'] == 'wordpress') {
            $container = 'dp-app-' . $env['APP_NAME'];
            $containerID = dp_get_container_id($container);

            if ($containerID) {
                $command1 = "docker exec --user " . SERVER_USER . " $container wp core update --path=/var/www/html";
                $process1 = new Process($command1);

                $command2 = "docker exec --user " . SERVER_USER . " $container wp plugin list --format=json --path=/var/www/html";
                $process2 = new Process($command2);

                try {
                    $output->writeln('Updating WordPress core in app: ' . $this->app . '...');
                    $process1->mustRun();

                    $output->writeln(trim($process1->getOutput()));

                    $process2->mustRun();
                    $plugins = json_decode($process2->getOutput());

                    if (!empty($env['APP_WP_UPDATE_PLUGINS'])) {
                        $updatePlugins = explode(',', $env['APP_WP_UPDATE_PLUGINS']);
                        $updateList = '';

                        if (is_array($plugins)) {
                            foreach ($plugins as $plugin) {
                                if (in_array($plugin->name, $updatePlugins)) {
                                    $updateList .= ' ' . $plugin->name;
                                }
                            }
                            if (!empty($updateList)) {
                                $output->writeln('Updating plugins:' . $updateList);
                                $command3 = "docker exec --user " . SERVER_USER . " $container wp plugin update $updateList --path=/var/www/html";
                                $process3 = new Process($command3);
                                $process3->mustRun();
                                $output->writeln(trim($process3->getOutput()));
                            }
                        }
                    }

                    dp_change_env_var($this->appDir, 'APP_WP_LAST_UPDATE', time());
                } catch (ProcessFailedException $e) {
                    throw new Exception($e->getMessage());
                }
            } else {
                throw new Exception("Can't find application container ID.");
            }
        } else {
            throw new Exception("WordPress isn't installed in app: $this->app.");
        }
    }
}
