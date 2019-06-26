<?php

namespace Control\Cli;

use Control\Internal\Context;
use Control\Internal\JobLog;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;


class CliStatus extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'status';


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Get status of control manager.')
            ->addOption('json', null, InputOption::VALUE_NONE, 'JSON output format.')
            ->addOption('watch', "w", InputOption::VALUE_OPTIONAL, 'Watch log and refresh each n seconds. Ignored if json format')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Get job progress and some other statuses');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $watch = intval($input->getOption("watch"));

        $jobStatus = ModuleJob::getJobStatus();
        if ($this->jsonMode) {
            $output->writeln(json_encode($jobStatus, JSON_PRETTY_PRINT));
        } else {

            /** @var ConsoleOutput $output */
            $output->getFormatter()->setStyle('running', new OutputFormatterStyle('cyan', null, ['blink']));
            $output->getFormatter()->setStyle('interrupted', new OutputFormatterStyle('red', null, ['blink']));
            $output->getFormatter()->setStyle('doing', new OutputFormatterStyle('cyan', null, ['blink']));
            $output->getFormatter()->setStyle('todo', new OutputFormatterStyle('yellow'));
            $output->getFormatter()->setStyle('installed', new OutputFormatterStyle('green'));
            $output->getFormatter()->setStyle('done', new OutputFormatterStyle('green'));
            $section = $output->section();
            if ($watch > 0) {
                while (true) {
                    $section->clear();
                    self::writeJobStatus($section, $jobStatus);
                    sleep($watch);
                    $jobStatus = ModuleJob::getJobStatus();
                }
            } else {
                self::writeJobStatus($section, $jobStatus);
                if (ModuleJob::hasFailed()) {
                    $helper = $this->getHelper('question');
                    $output->writeln("<info>The last job has failed.</info>");

                    $question = new ChoiceQuestion('<question>What do you what to do ?</question>', ['Retry', 'Ignore', 'Cancel'], "Cancel");
                    $answer = $helper->ask($input, $output, $question);

                    switch ($answer) {
                        case 'Retry':
                            $output->writeln("<info>Rerun Job.</info>");
                            ModuleManager::runJobInBackground();
                            break;

                        case 'Ignore' :
                            $output->writeln("<info>Rerun Job and ignore last error.</info>");
                            JobLog::markProcessFailedAsIgnored();
                            ModuleManager::runJobInBackground();
                            break;
                    }
                }
            }
        }
    }

    protected function writeJobStatus(ConsoleSectionOutput $section, $data)
    {
        if (!empty($data["tasks"])) {
            $table = new Table($section);

            $headers = ["<comment>Module</comment>", "Phase", "Process"];

            $table->setHeaders($headers);

            foreach ($data["tasks"] as $task) {

                $status = sprintf("<%s>%s</%s>", strtolower($task["status"]), $task["status"], strtolower($task["status"]));

                $row = [
                    sprintf("<comment>%s</comment>", $task["module"]),
                    sprintf("<info>%s</info>", ""),
                    sprintf("%s", $status)
                ];
                $table->addRow($row);

                $taskStatus = $task["status"] ?? "";
                if ($taskStatus === "RUNNING" || $taskStatus === "INTERRUPTED" || $taskStatus === "FAILED") {
                    foreach ($task["phases"] as $phase) {
                        $status = sprintf("<%s>%s</%s>", strtolower($phase["status"]), $phase["status"], strtolower($phase["status"]));

                        $row = [
                            sprintf("<comment>%s</comment>", ""),
                            sprintf("<info>%s</info>", $phase["name"]),
                            sprintf("<info>%s</info>", $status)
                        ];
                        $table->addRow($row);
                        if (isset($phase["process"])) {
                            foreach ($phase["process"] as $process) {
                                if ($process["status"] !== "DONE" && $process["status"] !== "TODO") {
                                    $status = sprintf("<%s>%s</%s>", strtolower($process["status"]), $process["status"], strtolower($process["status"]));
                                    $row = [
                                        sprintf("<comment>%s</comment>", ""),
                                        sprintf("<info>%s</info>", $process["label"]),
                                        sprintf("<info>%s</info>", $status)
                                    ];
                                    $table->addRow($row);
                                }
                                if (!empty($process["error"])) {
                                    $data["error"]=$process["error"];
                                }
                            }
                        }
                    }
                }
            }
            $table->setColumnMaxWidth(0, 30);
            $table->setColumnMaxWidth(1, 35);
            $table->setColumnWidths([20, 35, 10]);

            $table->render();
        } else {
            $section->writeln(sprintf("<info>%s.</info>", $data["status"]));
            if (! empty($data["message"])) {
                $section->writeln(sprintf("<comment>%s.</comment>", $data["message"]));
            }
        }
        if (!empty($data["error"])) {
            $section->writeln(sprintf("<error>%s</error>", $data["error"]));
        }
    }

    protected function getJobStatus()
    {

        $status = ["status" => "Activated"];


       if (ModuleJob::isRunning()) {
            $status = ModuleJob::getJobData();
            $status["status"] = "Running";
        } elseif (ModuleJob::hasFailed()) {
            $jobData = ModuleJob::getJobData();
            $status = $jobData;
        } else {
            $status["status"] = "Ready";
        }

        return $status;
    }
}