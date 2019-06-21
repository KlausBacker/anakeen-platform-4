<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


class CliGet extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'get';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Get parameter value.')
            ->addArgument("parameterName", InputArgument::OPTIONAL, "The parameter name")
            ->addOption('all', null, InputOption::VALUE_NONE, 'To show all module parameters')
            ->addOption('internal', null, InputOption::VALUE_NONE, 'To show all control parameters')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("get a parameter value");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $paramName = $input->getArgument("parameterName");
        if (!$paramName && !$input->getOption("all")) {
            throw new InvalidArgumentException(sprintf("Name argument is needed af --all option not set"));
        }
        $value = null;
        if ($paramName) {
            if ($input->getOption("internal")) {
                $controlParameters = Context::getControlParameters();
                $value = $controlParameters[$paramName] ?? null;
            } else {
                $value = Context::getParameterValue($paramName);
            }
            if ($value === null) {
                throw new RuntimeException(sprintf("Argument \"%s\" not found", $paramName));
            }
        }
        if ($this->jsonMode) {
            if ($input->getOption("all")) {
                if ($input->getOption("internal")) {
                    $output->writeln(json_encode(Context::getControlParameters(), JSON_PRETTY_PRINT));
                } else {
                    $output->writeln(json_encode(Context::getParameters(), JSON_PRETTY_PRINT));
                }
            } else {
                $output->writeln(json_encode([$paramName => $value], JSON_PRETTY_PRINT));
            }
        } else {
            if ($input->getOption("all")) {
                /** @var ConsoleOutput $output */
                if ($input->getOption("internal")) {
                    $this->writeAllParameters($output, Context::getControlParameters());
                } else {
                    $this->writeAllParameters($output, Context::getParameters());
                }
            } else {
                $output->writeln($value);
            }
        }
    }

    protected function writeAllParameters(ConsoleOutput $output, $parameters)
    {
        $section = $output->section();
        $table = new Table($section);

        $table->setHeaders(["Name", "Value"]);
        foreach ($parameters as $key => $value) {
            $table->addRow([
                sprintf("<comment>%s</comment> ", $key),
                $value !== '' ? "<info>$value</info>" : "<undefined>"
            ]);
        }
        $table->render();
    }
}