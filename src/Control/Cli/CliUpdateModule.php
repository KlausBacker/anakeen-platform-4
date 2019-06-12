<?php

namespace Control\Cli;

use Control\Internal\Module;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
            ->addArgument('module', InputArgument::OPTIONAL, "Module name tu update")
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command update all modules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $moduleName = $input->getArgument("module");
        if ($moduleName) {
            $module = new Module($moduleName);

            $module->getAvailableModule();
            $installedModule = $module->getInstalledModule();
            if (!$installedModule) {
                throw new InvalidArgumentException(sprintf("Installed Module \"%s\" not found", $moduleName));

            }
        } else {
             $module = new Module("");
        }
            if (!$module->preUpgrade()) {
                $output->writeln( "<info>No modules to update. All is up-to-date.</info>");
            } else {
                $module->displayModulesToProcess($output);
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('<question>Continue the update [y/N]?</question>', false);

                if (!$helper->ask($input, $output, $question)) {
                    return;
                }
                $output->writeln("Continue");
                $module->askAllParameters();
            }

    }

}