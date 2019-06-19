<?php

namespace Control\Cli;

use Control\Internal\JobLog;
use Control\Internal\ModuleJob;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CliDoJob extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'dojob';


    protected function configure()
    {
        $this
            ->setDescription('Run the job now.')
            ->setHidden(false)
            ->setHelp('Do the job');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        /** @var ConsoleOutput $output */
        JobLog::setOutput($output);
        if (ModuleJob::hasFailed()) {
            $output->writeln("<info>Retry last failed job</info>");
        }
        ModuleJob::runJob();
    }

}