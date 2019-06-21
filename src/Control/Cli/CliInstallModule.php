<?php

namespace Control\Cli;

use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command install all modules or one if module name is set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if (ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }

        if (!Context::getRepositories(true)) {
             throw new RuntimeException(sprintf("No one repositories configured. Use \"registry\" command to add."));
        }

        $moduleName = $input->getArgument("module");
        if ($moduleName) {
            $module = new ModuleManager($moduleName);
        } else {
            $module = new ModuleManager("");
        }
        if (!$module->prepareInstall()) {
            $output->writeln("<info>No modules to install. All is up-to-date.</info>");
        } else {
            $module->displayModulesToProcess($output);
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Continue the update [Y/n]?</question>', true);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
            AskParameters::askParameters($module, $this->getHelper('question'), $input, $output);
            $module->recordJob();
            $output->writeln("Job Recorded");
        }
    }

}