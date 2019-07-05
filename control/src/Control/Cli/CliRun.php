<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CliRun extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'run';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Run command')
            ->addArgument("shell", InputArgument::OPTIONAL, "The command to execute", "/bin/bash")
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Execute shell command"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $context=Context::getContext();

        $shellCommandArg=$input->getArgument("shell");
        try {
            if (strpos($shellCommandArg, " ") !== false) {
                $shellCommand=["/usr/bin/env", "sh", "-c", $shellCommandArg];
            } else {
                $shellCommand=[$shellCommandArg];
            }
            wiff_context_shell($context, $shellCommand);
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

}