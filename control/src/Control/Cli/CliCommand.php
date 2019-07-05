<?php

namespace Control\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CliCommand extends Command
{

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle('question', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('magenta', null, []));
        $output->getFormatter()->setStyle('ignored', new OutputFormatterStyle('magenta', null, []));
        $output->getFormatter()->setStyle('failed', new OutputFormatterStyle('red', null, ['blink']));
        $output->getFormatter()->setStyle('wait', new OutputFormatterStyle('yellow', null, ['blink']));
    }

}