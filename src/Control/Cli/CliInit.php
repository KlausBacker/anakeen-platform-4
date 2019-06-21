<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CliInit extends CliCommand
{
    // the name of the command (the part after "bin/console")
    const CONTEXT_NAME = "default";
    protected static $defaultName = 'init';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Init Anakeen Control.')
            ->addOption('pg-service', null, InputOption::VALUE_REQUIRED, 'Postgresql Service for Anakeen Platform')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command init Anakeen Control context.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if (Context::isInitialized()) {
            throw new RuntimeException(sprintf("Context already initialized"));
        }

        $wiff = \WIFF::getInstance();

        $contextPath = sprintf("%s/platform", realpath($wiff->getWiffRoot() . "../"));
        if (!is_dir($contextPath)) {
            $output->writeln("<info>mkdir $contextPath</info>");
            mkdir($contextPath);
        }

        AskParameters::setParameters([
            "core_db" => $input->getOption("pg-service")
        ]);

        Context::init();

        $ret = $context = $wiff->createContext(self::CONTEXT_NAME, $contextPath, "Anakeen Platform Context", "");
        if ($ret === false) {
            throw new RuntimeException($wiff->errorMessage);
        }

        $output->writeln("<info>Anakeen Platform Context is initialized</info>");

    }

}