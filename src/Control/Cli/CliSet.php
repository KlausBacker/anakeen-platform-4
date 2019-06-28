<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CliSet extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'set';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Set control parameter value')
            ->addArgument("parameterName", InputArgument::REQUIRED, "The parameter name")
            ->addArgument("value", InputArgument::REQUIRED, "New parameter value")
            ->addOption('module', null, InputOption::VALUE_NONE, 'To set a module parameter')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Modify a parameter key for anakeen-control or for a module if <info>module</info> option is set"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $paramName = $input->getArgument("parameterName");
        $value = $input->getArgument("value");

        if ($input->getOption("module")) {

            $controlParameters = Context::getParameters();
            if (!isset($controlParameters[$paramName])) {
                throw new RuntimeException(sprintf("Module parameter \"%s\" not found", $paramName));
            }
            Context::setParameter($paramName, $value);
            $results = Context::getParameters();
            $output->writeln(sprintf("Module parameter \"%s\" set to \"%s\"", $paramName, $results[$paramName]));
            $output->writeln(sprintf("<info>Internal parameter \"<comment>%s</comment>\" set to \"<comment>%s</comment>\".</info>", $paramName, $results[$paramName]));
        } else {
            $controlParameters = Context::getControlParameters();
            if (!isset($controlParameters[$paramName])) {
                throw new RuntimeException(sprintf("Internal parameter \"%s\" not found", $paramName));
            }
            Context::setControlParameter($paramName, $value);
            Context::setParameter($paramName, $value);
            $results = Context::getParameters();
            $output->writeln(sprintf("<info>Internal parameter \"<comment>%s</comment>\" set to \"<comment>%s</comment>\".</info>", $paramName, $results[$paramName]));
        }
    }

}