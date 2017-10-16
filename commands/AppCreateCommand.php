<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AppCreateCommand extends Command
{
    /**
     * The application name.
     *
     * @var string
     */
    protected $appName = '';

    /**
     * The application template.
     *
     * @var string
     */
    protected $appTemplate = '';

    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:create')
             ->setDescription('Create an application.')
             ->setHelp('This command creates an application.');
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if( $this->userInput($input, $output)) {
            if($this->createAppDir($output)) {
                $output->writeln('<info>Application created!</info>');
            }
        }
    }

    /**
     * Ask user for name and stack.
     *
     * @return bool
     */
    protected function userInput($input, $output)
    {
        $questionHelper = $this->getHelper('question');

        // ask for application name
        $question = new Question('Application name? ');
        $this->appName = trim($questionHelper->ask($input, $output, $question));

        // ask for stack name
        $templates = array_values(sp_get_stacks());

        // ask for template
        $question = new ChoiceQuestion(
            'Please select a stack:',
            $templates, 0
        );
        $question->setErrorMessage('Stack %s is invalid.');
        $this->appTemplate = $questionHelper->ask($input, $output, $question);

        return true;
    }

    /**
     * Create the application directory from a template.
     *
     * @return bool
     */
    protected function createAppDir($output)
    {
        $output->writeln("Creating application directory...");

        if( $this->appName && $this->appTemplate )
        {
            $appSlug = sp_create_slug($this->appName);
            $appDir = SERVER_APP_DIR . '/' . $appSlug;
            $stackDir = SERVER_STACK_DIR . '/' . $this->appTemplate . '/1.0';

            $dbPassword = sp_random_password(16);
            $dbUser = $appSlug;
            $dbName = $appSlug.'_'.sp_random_password(6);

            $command = "docker exec sp-db bash -c \"MYSQL_PWD=".MYSQL_ROOT_PASSWORD." mysql -u root -e ".'\"'."CREATE DATABASE IF NOT EXISTS $dbName; CREATE USER '$dbUser'@'%' IDENTIFIED BY '$dbPassword'; GRANT ALL ON $dbName.* TO '$dbUser'@'%';".'\"'."\"";
            $process = new Process($command);

            if(! file_exists($appDir)) {
                sp_copy_directory($stackDir, $appDir);

                try {
                    $output->writeln("Creating database...");
                    $process->mustRun();
                    sp_change_env_var($appDir, 'APP_DB_USER', $dbUser);
                    sp_change_env_var($appDir, 'APP_DB_DATABASE', $dbName);
                    sp_change_env_var($appDir, 'APP_DB_USER_PASSWORD', $dbPassword);
                } catch (ProcessFailedException $e) {
                    $output->writeln("<error>".$e->getMessage()."</error>");
                }

                sp_change_env_var($appDir, 'APP_NAME', $appSlug);
                sp_change_env_var($appDir, 'APP_DOMAINS', $appSlug.'.dev');
                sp_change_env_var($appDir, 'APP_SFTP_PASS', crypt(md5(uniqid()), 'serverpilot'));

                return true;
            } else {
                $output->writeln("<error>Application directory already exists.</error>");
            }
        }
        return false;
    }
}
