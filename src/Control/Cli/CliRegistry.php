<?php

namespace Control\Cli;

use Control\Internal\Context;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CliRegistry extends CliCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'registry';

    protected $supportedActions = ["add", "remove"];


    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Manage registries.')
            ->addArgument("action", InputArgument::REQUIRED, sprintf("%s", implode(", ", $this->supportedActions)))
            ->addArgument("name", InputArgument::REQUIRED, "Name to identify repository")
            ->addArgument("url", InputArgument::OPTIONAL, "Url to the repository")
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Manage the set of repositories.\n".
            "<info>Add repository:</info><comment>anakeen-control registry add myrepo https://...</comment>\n".
            "<info>Remove repositoy:</info><comment>anakeen-control registry remove myrepo</comment>\n"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument("action");
        $name = $input->getArgument("name");
        $url = $input->getArgument("url");

        if (!in_array($action, $this->supportedActions)) {
            throw new InvalidArgumentException(sprintf('Unsupported actions are :  "%s".', implode(", ", $this->supportedActions)));
        }
        parent::execute($input, $output);

        Context::init();

        switch ($action) {
            case "add":
                Context::addRepository($name, $url);
                break;
            case "remove":
                Context::removeRepository($name);
                $output->writeln(sprintf("<info>Repository \"%s\" is removed", $name));
                break;
        }

    }

}