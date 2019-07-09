<?php

namespace Control\Cli;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


class CliLog extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'log';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Show jobs log')
            ->addOption('lines', "-l", InputOption::VALUE_OPTIONAL, 'Display last log lines number', 200)
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Display log files written by previous job');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $info = \Control\Internal\Log::getLogData($input->getOption("lines"));
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

        $headers = ["<comment>Date</comment>", "Module", "Phase", "Task", "Info"];
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $headers[] = "Vendor";
        }
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $headers[] = "Available version";
        }
        $table->setHeaders($headers);


        $table->setColumnMaxWidth(1, 20);
        $table->setColumnMaxWidth(2, 19);
        $table->setColumnMaxWidth(3, 20);
        $table->setColumnMaxWidth(4, 60);
        $table->setColumnWidths([10, 20, 10, 10, 30]);

        foreach ($info as $log) {
            if (is_array($log["value"])) {
                $log["value"] = implode(", ", $log["value"]);
            }
            $tag = "info";
            if ($log["task"] === "error") {
                $tag = "error";
            }
            $row = [
                sprintf("<comment>%s</comment>", $log["date"]),
                sprintf("<$tag>%s</$tag>", $log["module"]),
                sprintf("<$tag>%s</$tag>", $log["phase"]),
                sprintf("<$tag>%s</$tag>", $log["task"]),
                sprintf("<$tag>%s</$tag>", $log["value"])
            ];


            $table->addRow($row);
        }
        $table->render();
    }
}