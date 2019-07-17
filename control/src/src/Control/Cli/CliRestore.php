<?php

namespace Control\Cli;

use Control\Internal\ArchiveContext;
use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;
use Control\Internal\RestoreContext;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CliRestore extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'restore';


    protected function configure()
    {
        parent::configure();
        $vaultPath = realpath(__DIR__ . "/../../../..") . "/vaults";
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Restore an archive of Anakeen Platform.')
            ->addOption('pg-service', null, InputOption::VALUE_REQUIRED, 'The new database service')
            ->addOption('vaults-path', null, InputOption::VALUE_REQUIRED, 'The vault-path to store archived vaults', $vaultPath)
            ->addOption('force-clean', null, InputOption::VALUE_NONE, 'To use an exiting database')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Not launch job')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command restore an complete Anakeen Platform context from archive file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }

        if (!$input->getOption("pg-service")) {
            throw new InvalidOptionException("Option \"pg-service\" is mandatory");
        }
        if (!$input->getOption("vaults-path")) {
            throw new InvalidOptionException("Option \"vaults-path\" is mandatory");
        }

        $tasks = [
            "module" => "restoring",
            "status" => ModuleJob::TODO_STATUS,
            "phases" => [
                ["name" => RestoreContext::PHASE_PGRESTORE, "status" => ModuleJob::TODO_STATUS],
                ["name" => RestoreContext::PHASE_RECONFIGURE, "status" => ModuleJob::TODO_STATUS],
            ]
        ];

        ModuleJob::recordJobTask([
            "status" => ModuleJob::TODO_STATUS,
            "action" => "restore",
            "pg-service" => $input->getOption("pg-service"),
            "vaults-path" => $input->getOption("vaults-path"),
            "force-clean" => $input->getOption("force-clean"),
            "tasks" => [$tasks],
        ]);

        if (!$input->getOption("dry-run")) {
            ModuleManager::runJobInBackground();
            $output->writeln("<info>Restore in progress</info>");
        } else {
            $output->writeln("<info>Restore job is recorded</info>");
        }



    }

}