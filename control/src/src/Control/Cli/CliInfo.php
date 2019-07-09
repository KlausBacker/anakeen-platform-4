<?php

namespace Control\Cli;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CliInfo extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'info';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Get information about installation.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command get module info and accounts info');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input,$output);
        $info = \Control\Internal\Info::getInfo();
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

// display the table with the known contents
        foreach ($info as $key => $value) {
            if (is_array($value)) {
                $this->writeColor($output, $value);
            } else {
                $table->addRow([sprintf("<comment>%s</comment> ", $key), sprintf(" <info>%s</info>", $value)]);
            }
        }
        $table->render();
    }
}