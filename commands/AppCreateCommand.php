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
     * Ask user for name and template.
     *
     * @return bool
     */
    protected function userInput($input, $output)
    {
        $questionHelper = $this->getHelper('question');

        // ask for application name
        $question = new Question('Application name? ');
        $this->appName = trim($questionHelper->ask($input, $output, $question));

        // ask for template name
        $templates = array_values(sp_get_templates());

        // ask for template
        $question = new ChoiceQuestion(
            'Please select a template:',
            $templates, 0
        );
        $question->setErrorMessage('Template %s is invalid.');
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
            $templateDir = SERVER_TEMPLATE_DIR . '/' . $this->appTemplate;

            if(! file_exists($appDir)) {
                sp_copy_directory($templateDir, $appDir);
                return true;
            } else {
                $output->writeln("<error>Application directory already exists.</error>");
            }
        }
        return false;
    }
}
