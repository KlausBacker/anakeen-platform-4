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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CliRemoveModule extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'remove';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Remove module.')
            ->addArgument('module', InputArgument::REQUIRED, "Module name to remove")
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command remove a module');
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

        $module = new ModuleManager($moduleName);

        $module->prepareRemove();

        $context = Context::getContext();
        if ($context->warningMessage) {
            $output->writeln(sprintf("<warning>%s</warning>", $context->warningMessage));
        }
        $module->displayModulesToProcess($output);
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>Confirm remove module [Y/n]?</question>', true);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        $module->recordJob();
        LibSystem::purgeTmpFiles();
        $output->writeln("Job Recorded");


    }

}