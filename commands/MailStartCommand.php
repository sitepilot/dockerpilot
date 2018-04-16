<?php

namespace Dockerpilot\Command;

use Exception;
use Philo\Blade\Blade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MailStartCommand extends Command
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('mail:start')
            ->setDescription('Starts the mail relay server.')
            ->setHelp('This command starts the mail relay server.');
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
            $this->createConfig($output);
            $this->startServer($output);
            $output->writeln("<info>Mail relay server started!</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to start mail relay server: \n" . $e->getMessage() . "</error>");
        }
    }

    /**
     * Create mail relay configuration.
     *
     * @param $output
     * @return void
     */
    protected function createConfig(OutputInterface $output)
    {
        $output->writeln("Generating mail relay stack file...");
        $filePath = SERVER_WORKDIR . '/stacks/mail/mail.blade.php';

        $bladeFolder = SERVER_WORKDIR . '/stacks/mail';
        $cache = SERVER_WORKDIR . '/data/cache';
        $views = dp_path($bladeFolder);
        $mail = dp_get_config('mail');

        if (file_exists($filePath)) {
            $blade = new Blade($views, $cache);
            $content = $blade->view()->make('mail', ['mail' => $mail])->render();
            $destFile = dp_path(SERVER_WORKDIR . '/stacks/mail/config/mail.yml');
            $writeFile = fopen($destFile, "w") or die("Unable to open file!");
            fwrite($writeFile, $content);
            fclose($writeFile);
        }
    }

    /**
     * Start mail relay server.
     *
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function startServer(OutputInterface $output)
    {
        $output->writeln("Starting mail relay server, please wait...");
        $process = new Process('cd ' . SERVER_WORKDIR . '/stacks/mail/config && docker stack deploy -c mail.yml mail');
        $process->setTimeout(3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
