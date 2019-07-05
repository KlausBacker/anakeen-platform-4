<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


class CliRegistry extends CliJsonCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'registry';

    protected $supportedActions = ["add", "remove", "show"];


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Manage registries.')
            ->addArgument("action", InputArgument::REQUIRED, sprintf("%s", implode(", ", $this->supportedActions)))
            ->addArgument("name", InputArgument::OPTIONAL, "Name to identify repository")
            ->addArgument("url", InputArgument::OPTIONAL, "Url to the repository")
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Manage the set of repositories.\n" .
                "Show repositories:<comment>anakeen-control registry show</comment>\n" .
                "Add repository:   <comment>anakeen-control registry add myrepo https://...</comment>\n" .
                "Remove repositoy: <comment>anakeen-control registry remove myrepo</comment>"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument("action");
        $name = $input->getArgument("name");
        $url = $input->getArgument("url");

        if (!in_array($action, $this->supportedActions)) {
            throw new CommandNotFoundException(sprintf('Unsupported actions are :  "%s".', implode(", ", $this->supportedActions)));
        }
        parent::execute($input, $output);

        Context::init();

        switch ($action) {
            case "add":
                if (!$name) {
                    throw new InvalidArgumentException(sprintf("Name argument is needed for add action"));
                }
                if (!$url) {
                    throw new InvalidArgumentException(sprintf("Url argument is needed for add action"));
                }
                Context::addRepository($name, $url);
                break;
            case "remove":
                if (!$name) {
                    throw new InvalidArgumentException(sprintf("Name argument is needed for add action"));
                }
                Context::removeRepository($name);
                $output->writeln(sprintf("<info>Repository \"%s\" is removed", $name));
                break;
            case "show":
                $repo = Context::getRepositories();
                if ($this->jsonMode) {
                    $output->writeln(json_encode($repo, JSON_PRETTY_PRINT));
                } else {
                    /** @var ConsoleOutput $output */
                    if ($repo) {
                        $this->writeRepoTable($output, $repo);
                    } else {
                        $output->writeln("<comment>No one registries recorded.</comment>");
                        $output->writeln("<comment>Use \"<info>registry add</info>\" to record it.</comment>");
                    }
                }
                break;
        }
    }

    protected function writeRepoTable(ConsoleOutput $output, $repositories)
    {
        $section = $output->section();
        $table = new Table($section);

// display the table with the known contents
        $table->setHeaders(["Name", "Url", "Ping", "Activated"]);
        foreach ($repositories as $key => $repo) {
            /** @var \Repository $repo */

            $table->addRow([
                sprintf("<comment>%s</comment> ", $repo->name),
                sprintf(" <info>%s</info>", $repo->getUrl()),
                sprintf(" <info>%s</info>", $repo->isValid() ? "Valid" : "<error>Not valid</error>"),
                sprintf(" <info>%s</info>", $repo->status === "activated" ? "Activated" : "<error>Disabled</error>")
            ]);

        }
        $table->render();
    }
}