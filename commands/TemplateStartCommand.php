<?php
namespace Serverpilot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TemplateStartCommand extends Command
{
    /**
     * The template name.
     *
     * @var string
     */
    protected $templateName = '';

    /**
     * The template dir.
     *
     * @var string
     */
    protected $templateDir = '';

    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('template:start')
             ->setDescription('Start a template.')
             ->setHelp('This command starts a template.')
             ->addOption('templateName', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * Execute command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->userInput($input, $output)) {
            if($this->startTemplate($output)) {
                $output->writeln('<info>Template started!</info>');
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
        $templates = sp_get_templates();

        if(is_array($templates) && count($templates) > 0) {
            $startTemplates = array();

            // Check which templates are not running
            foreach($templates as $dir=>$template) {
                sp_load_env($dir);
                if($appName = getenv('APP_NAME')) {
                    $id = sp_get_container_id("serverpilot-app-".$appName);

                    if(!$id) {
                        $startTemplates[] = $template;
                    }
                }
            }
            if(count($startTemplates) > 0) {
                if( ! $input->getOption('templateName') ) {
                    // ask for appication
                    $question = new ChoiceQuestion(
                        'Which template would you like to start?',
                        $startTemplates, 0
                    );
                    $question->setErrorMessage('Template %s is invalid.');
                    $this->templateName = $questionHelper->ask($input, $output, $question);
                } else {
                    $this->templateName = $input->getOption('templateName');
                }

                $this->templateDir  = sp_path(SERVER_TEMPLATE_DIR . '/' . $this->templateName);

                return true;
            } else {
                $output->writeln("<info>All templates are running.</info>");
            }
        } else {
            $output->writeln("<error>Couldn't find any templates, create or install a template!</error>");
        }

        return false;
    }

    /**
     * Starts the template.
     *
     * @return bool
     */
    protected function startTemplate($output) {
        sp_load_env($this->templateDir);
        $output->writeln("Starting template ".$this->templateName.", please wait...");
        $process = new Process('cd '.$this->templateDir.' && docker-compose up -d');

        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>");
        }

        return false;
    }

}
