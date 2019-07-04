<?php

namespace Control\Cli;

use Control\Internal\ModuleJob;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CliKill extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'kill';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Kill running job')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Kill running job"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $pid = ModuleJob::killJob();
        $output->writeln(sprintf("<info>Kill process %d.</info>", $pid));
    }

}