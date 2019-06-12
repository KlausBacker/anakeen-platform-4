<?php

namespace Control\Cli;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


class CliShow extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'show';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Get installed modules.')
            ->addOption('json', null, InputOption::VALUE_NONE, 'JSON output format.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Get all installed modules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $info = \Control\Internal\Info::getModuleList();
        if ($this->jsonMode) {
            $output->writeln(json_encode($info, JSON_PRETTY_PRINT));
        } else {

            // $output->writeln($info);
            /** @var ConsoleOutput $output */
            $this->writeColor($output, $info);
        }
    }

    protected function writeColor(ConsoleOutput $output, $info)
    {

        $section = $output->section();
        $table = new Table($section);

        $headers = ["<comment>Module</comment>", "Description", "Version"];
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $headers[] = "Vendor";
        }
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $headers[] = "Available version";
        }
        $table->setHeaders($headers);

        foreach ($info as $module) {
            /** @var \Module $module */
            $row = [
                sprintf("<comment>%s</comment>", $module->name),
                sprintf("<info>%s</info>", $module->description),
                sprintf("<info>%s</info>", $module->version)
            ];

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $row[] = sprintf("<info>%s</info>", $module->vendor);
            }
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $row[] = sprintf("<info>%s</info>", $module->availableversion);
            }

            $table->addRow($row);
        }
        $table->render();
    }
}