<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\InvalidOptionException;
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
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Initial password for web access (control and platform)')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command init Anakeen Control context.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pgService = $input->getOption("pg-service");
        if (!$pgService) {
            throw new InvalidOptionException(sprintf('"pg-service" option is needed'));
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        try {
            if (!@pg_connect(sprintf('service=%s', $pgService))) {
                throw new InvalidOptionException(sprintf('Cannot access to database server : '));
            }
        } catch (\ErrorException $e) {
            throw new InvalidOptionException(sprintf('Cannot access to database server : %s', $e->getMessage()));
        }

        if (! $input->getOption("password")) {
            throw new InvalidOptionException(sprintf('"password" option is needed'));
        }
        parent::execute($input, $output);

        if (Context::isInitialized()) {
            throw new RuntimeException(sprintf("Context already initialized"));
        }

        $wiff = \WIFF::getInstance();

        $rootContextPath = sprintf(realpath($wiff->getWiffRoot() . "../"));
        $contextPath = sprintf("%s/platform", $rootContextPath);
        if (!is_dir($contextPath)) {
            $output->writeln("<info>mkdir $contextPath</info>");
            if (!mkdir($contextPath)) {
                throw new RuntimeException(sprintf("Cannot create platform directory '%s'", $contextPath));
            }
        }
        if (!is_writable($contextPath)) {
            throw new RuntimeException(sprintf("The platform directory '%s' is not writable", $contextPath));
        }

        AskParameters::setParameters([
            "core_db" => $pgService,
            "core_admin_passwd" => $input->getOption("password")
        ]);

        Context::init();
        $wiff->createPasswordFile("admin", $input->getOption("password"));

        $ret = $context = $wiff->createContext(self::CONTEXT_NAME, $contextPath, "Anakeen Platform Context", "");
        if ($ret === false) {
            Context::reset();
            throw new RuntimeException($wiff->errorMessage);
        }

        $output->writeln("<info>Anakeen Platform Context is initialized</info>");

    }

}
