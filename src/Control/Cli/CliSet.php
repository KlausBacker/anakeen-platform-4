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
            ->setDescription('Manage registries.')
            ->addArgument("parameterName", InputArgument::REQUIRED, "The parameter name")
            ->addArgument("value", InputArgument::REQUIRED, "new value of parameter")
            ->addOption('internal', null, InputOption::VALUE_NONE, 'To set a control parameters')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Modify a parameter key for a module or for anakeen-control if <info>internal</info> option is set"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $paramName = $input->getArgument("parameterName");
        $value = $input->getArgument("value");

        if ($input->getOption("internal")) {
            $controlParameters = Context::getControlParameters();
            if (!isset($controlParameters[$paramName])) {
                throw new RuntimeException(sprintf("Internal parameter \"%s\" not found", $paramName));
            }
            Context::setControlParameter($paramName, $value);

        } else {

            $controlParameters = Context::getParameters();
            if (!isset($controlParameters[$paramName])) {
                throw new RuntimeException(sprintf("Module parameter \"%s\" not found", $paramName));
            }
            Context::setParameter($paramName, $value);
        }
    }

}