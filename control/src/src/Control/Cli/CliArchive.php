<?php

namespace Control\Cli;

use Control\Internal\ArchiveContext;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CliArchive extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'archive';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Archive Anakeen Platform.')
            ->addArgument('file', InputArgument::REQUIRED, 'Output file (.zip file)')
            ->addOption('without-vault', null, InputOption::VALUE_NONE, 'No save vault')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Not launch job')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command save application files, database and vaults.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }

        $outputFile = $input->getArgument("file");
        if (!$outputFile) {
            throw new InvalidOptionException("Argument \"file\" is mandatory");
        }
        if (file_exists($outputFile) && !is_writable($outputFile)) {
            throw new InvalidOptionException("Output file is not writable");
        } else {
            if (!file_put_contents($outputFile, "") === false) {
                throw new InvalidOptionException("Output file is not writable");
            }
        }

        $tasks = [
            "module" => "archiving",
            "status" => ModuleJob::TODO_STATUS,
            "phases" => [
                ["name" => ArchiveContext::PHASE_PLATFORM, "status" => ModuleJob::TODO_STATUS],
                ["name" => ArchiveContext::PHASE_CONTROL, "status" => ModuleJob::TODO_STATUS],
                ["name" => ArchiveContext::PHASE_DATABASE, "status" => ModuleJob::TODO_STATUS],
                ["name" => ArchiveContext::PHASE_VAULTS, "status" => ModuleJob::TODO_STATUS]
            ]
        ];

        ModuleJob::recordJobTask([
            "status" => ModuleJob::TODO_STATUS,
            "action" => "archive",
            "output" => $input->getArgument("file"),
            "with-vault" => !$input->getOption("without-vault"),
            "tasks" => [$tasks],
        ]);
        if (!$input->getOption("dry-run")) {
            ModuleManager::runJobInBackground();

            $output->writeln("<info>Job is processing</info>");
            $output->writeln(
                sprintf(
                    "<comment>Archive file will be saved to \"%s\"</comment>",
                    $input->getArgument("file")
                )
            );
            if ($input->getOption("without-vault")) {
                $output->writeln("<comment>Vaults are not saved.</comment>");
            }
        }
    }


}
