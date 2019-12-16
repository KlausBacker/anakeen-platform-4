<?php

namespace Control\Cli;

use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CliUpdateModule extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'update';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Update module.')
            ->addArgument('module', InputArgument::OPTIONAL, "Module name to update")
            ->addOption("force", null, InputOption::VALUE_NONE, "Force downgrade")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Not launch job')
            ->addOption('background-job', null, InputOption::VALUE_NONE, 'job run directly and wait the end')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command update all modules or one if module name is set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if (ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }

        $output->getFormatter()->setStyle('question', new OutputFormatterStyle('cyan', null, []));
        $moduleName = $input->getArgument("module");
        if ($moduleName) {
            $module = new ModuleManager($moduleName);
        } else {
            $module = new ModuleManager("");
        }
        if (!$module->prepareUpgrade($input->getOption("force"))) {
            $output->writeln("<info>No modules to update. All is up-to-date.</info>");
        } else {
            $module->displayModulesToProcess($output);
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion('<question>Continue the update [Y/n]?</question>', true);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }

            /** @var  ConsoleOutput $output */
            AskParameters::askParameters($module, $this->getHelper('question'), $input, $output);
            if (!$input->getOption("background-job")) {
                $output->writeln("Updating modules...");
            }
            $module->recordJob($input->getOption("dry-run"));
            if ($input->getOption("dry-run")) {
                $output->writeln("Job recorded.");
            } else {
                if ($input->getOption("background-job")) {
                    $output->writeln("Background job running");
                } else {
                    ModuleJob::waitRunning($output);
                    $output->writeln("Update complete.");
                }
            }
        }
    }

    protected function askParameters(ModuleManager $module, InputInterface $input, OutputInterface $output)
    {
        $askParameters = $module->getAllParameters();
        $modules = $module->getDepencies();
        foreach ($modules as $module) {
            $askModuleParameters = array_filter($askParameters, function ($ask) use ($module) {
                /** @var \Module $module */
                return $ask["module"] === $module->name;
            });
            foreach ($askModuleParameters as $ask) {
                if ($module->needphase === 'install') {
                    $this->askParameter($ask, $input, $output);
                }
                if ($module->needphase === 'update') {
                    if ($ask["onupgrade"] ?? "" === "W") {
                        $this->askParameter($ask, $input, $output);
                    }
                }
            }
        }
    }

    protected function askParameter($ask, InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $output->getFormatter()->setStyle('qd', new OutputFormatterStyle('yellow'));
        $questionLabel = $ask["label"];
        if (!empty($ask["default"])) {
            $questionLabel .= sprintf(" <qd>[%s]</qd>", $ask["default"]);
        }
        $questionLabel .= "?";
        $questionLabel = sprintf('<question>%s </question>', $questionLabel);
        $availableAnswers = [];
        if ($ask["type"] ?? "" === "enum") {
            $availableAnswers = explode("|", $ask["values"]);
            $question = new ChoiceQuestion($questionLabel, $availableAnswers, $ask["default"] ?? "");
        } else {
            $question = new Question($questionLabel, $ask["default"] ?? "");
        }
        $question->setValidator(function ($answer) use ($ask, $availableAnswers) {
            if (!empty($ask["needed"]) && $ask["needed"] === "Y") {
                if (empty($answer)) {
                    throw new \RuntimeException(
                        'This value is required'
                    );
                }
            }
            if ($availableAnswers && !in_array($answer, $availableAnswers)) {
                throw new \RuntimeException(
                    sprintf('Value must be one of "%s"', implode('", "', $availableAnswers))
                );
            }
            return $answer;
        });


        $response = $helper->ask($input, $output, $question);

        return $response;
    }
}
