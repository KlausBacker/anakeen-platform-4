<?php

namespace Control\Cli;

use Stecman\Component\Symfony\Console\BashCompletion\Completion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;

class CliBashCompletion extends \Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand
{
    protected function configureCompletion(CompletionHandler $handler)
    {
        $handler->addHandlers([
            // Instances of Completion go here.
            // See below for examples.
            new Completion\ShellPathCompletion(
                "archive",                    // match command name
                'file',
                Completion::TYPE_ARGUMENT
            ),
            new Completion(
                'remove',                    // match command name
                'module',               // match argument/option name
                Completion::TYPE_ARGUMENT, // match definition type (option/argument)
                function () {
                    $info = \Control\Internal\Info::getInstalledModuleList();
                    $modules = [];
                    foreach ($info as $module) {
                        $modules[] = $module->name;
                    }
                    return $modules;
                }
            ),
            new Completion(
                'update',                    // match command name
                'module',               // match argument/option name
                Completion::TYPE_ARGUMENT, // match definition type (option/argument)
                function () {
                    $info = \Control\Internal\Info::getModuleOutdatedList();
                    $modules = [];
                    foreach ($info as $module) {
                        $modules[] = $module->name;
                    }
                    return $modules;
                }
            ),

            new Completion(
                'install',
                'module',
                Completion::TYPE_ARGUMENT,
                function () {
                    $info = \Control\Internal\Info::getAllModuleList();
                    $modules = [];
                    foreach ($info as $module) {
                        if (!$module->status) {
                            $modules[] = $module->name;
                        }
                    }
                    return $modules;
                }
            )
        ]);
    }
}