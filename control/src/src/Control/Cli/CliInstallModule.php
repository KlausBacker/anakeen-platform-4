<?php

namespace Control\Cli;

use Control\Internal\Context;
use Control\Internal\LibSystem;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CliInstallModule extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'install';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Install module.')
            ->addArgument('module', InputArgument::OPTIONAL, "Module name to install")
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, '.app file to install.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Not launch job')
            ->addOption('background-job', null, InputOption::VALUE_NONE, 'job run in background task')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command install all modules or one if module name is set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if (ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. wait or kill it"));
        }


        /** @var  ConsoleOutput $output */
        $section = $output->section();
        $section->writeln("<wait>Searching modules on repositories...</wait>");

        if (!Context::getRepositories(true)) {
            throw new RuntimeException(sprintf("No one repositories configured. Use \"registry\" command to add."));
        }

        $file = $input->getOption("file");

        $force = false;
        $moduleName = $input->getArgument("module");
        if ($file) {
            $module = new ModuleManager("");
            $module->setFile($file);
            $force = true;
        } elseif ($moduleName) {
            $module = new ModuleManager($moduleName);
        } else {
            $module = new ModuleManager("");
        }
        if (!$module->prepareInstall($force)) {
            $section->clear();
            $output->writeln("<info>No modules to install. All is up-to-date.</info>");
        } else {
            $section->clear();
            $context = Context::getContext();
            if ($context->warningMessage) {
                $output->writeln(sprintf("<warning>%s</warning>", $context->warningMessage));
            }
            $module->displayModulesToProcess($output);
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Continue the update [Y/n]?</question>', true);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
            AskParameters::askParameters($module, $this->getHelper('question'), $input, $output);
            if (!$input->getOption("background-job")) {
                $output->writeln("Installing modules...");
            }
            $module->recordJob($input->getOption("dry-run"));
            LibSystem::purgeTmpFiles();
            if ($input->getOption("dry-run")) {
                $output->writeln("Job recorded.");
            } else {
                if ($input->getOption("background-job")) {
                    $output->writeln("Background job running");
                } else {
                    ModuleJob::waitRunning($output);

                    $output->writeln("Install complete.");
                }
            }
        }
        return 0;
    }
}