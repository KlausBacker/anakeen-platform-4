<?php

namespace Control\Cli;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CliOutdated extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'outdated';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Get outdated modules.')

            ->addOption('format', null, InputOption::VALUE_OPTIONAL, ' Output format [json].')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Get all installed modules which need upgrade');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       parent::execute($input, $output);

        $info = \Control\Internal\Info::getModuleOutdatedList();
        if ($this->jsonMode) {
            $output->writeln(json_encode($info, JSON_PRETTY_PRINT));
        } else {

            // $output->writeln($info);
            /** @var ConsoleOutput $output */
            if ($info) {
                $this->writeColor($output, $info);
            } else {
                 $output->writeln("<info>All modules are up-to-date</info>");
            }
        }
    }

    protected function writeColor(ConsoleOutput $output, $info)
    {

        $section = $output->section();
        $table = new Table($section);

        $table->setHeaders(["<comment>Module</comment>", "Description", "Version", "Available version"]);
// display the table with the known contents
        foreach ($info as $module) {
            /** @var \Module $module */
                $table->addRow([
                    sprintf("<comment>%s</comment>", $module->name),
                    sprintf("<info>%s</info>", $module->description),
                    sprintf("<info>%s</info>", $module->version),
                    sprintf("<info>%s</info>", $module->availableversion)
                ]);
        }
        $table->render();
    }
}