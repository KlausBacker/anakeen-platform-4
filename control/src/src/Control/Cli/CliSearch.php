<?php

namespace Control\Cli;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


class CliSearch extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'search';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Search modules.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Get all modules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $info = \Control\Internal\Info::getAllModuleList();
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

        $headers = ["<comment>Module</comment>", "Description", "Version", "Status"];
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $headers[] = "Vendor";
        }
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {

            $headers = ["<comment>Module</comment>", "Description", "Installed version", "Status"];
            $headers[] = "Vendor";
            $headers[] = "Available version";
        }
        $table->setHeaders($headers);

        foreach ($info as $module) {
            /** @var \Module $module */
            $status = $module->status;
            if ($module->canUpdate) {
                $status = "<warning>Outdated</warning>";
            } elseif (!$status) {
                $status = "Uninstalled";
            }if ($status === "installed") {
                $status = "<info>Installed</info>";
            }

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                if ($status === "Uninstalled") {
                    $module->availableversion = $module->version;
                    $module->version = '';
                }
            }
            $row = [
                sprintf("<comment>%s</comment>", $module->name),
                sprintf("<info>%s</info>", $module->description),
                sprintf("<info>%s</info>", $module->version),

                sprintf("%s", $status)
            ];

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $row[] = sprintf("<info>%s</info>", $module->vendor);
            }
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {

                if ($module->availableversion !== $module->version) {
                    $row[] = sprintf("<info>%s</info>", $module->availableversion);
                } else {
                    $row[] = "";
                }
            }

            $table->addRow($row);
        }
        $table->render();
    }
}