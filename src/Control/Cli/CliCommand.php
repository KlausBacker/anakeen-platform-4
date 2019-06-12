<?php

namespace Control\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CliCommand extends Command
{
    protected $jsonMode = false;

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, ' Output format [json].');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption("format") && $input->getOption("format")!=="json") {
            throw new InvalidArgumentException(sprintf('Unsupported format "%s". Only "json" supported.', $input->getOption("format")));
        }
        $this->jsonMode = $input->getOption("format") === "json";

    }

}