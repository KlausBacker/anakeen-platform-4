<?php

namespace Control\Cli;

use Control\Internal\Context;
use Control\Internal\ModuleManager;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AskParameters
{
    const askFile = "conf/askes.json";
    protected static $askParameters = null;

    public static function askParameters(ModuleManager $moduleMng, QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $askParameters = $moduleMng->getAllParameters();
        $modules = $moduleMng->getDepencies();
        foreach ($modules as $module) {
            $askModuleParameters = array_filter($askParameters, function ($ask) use ($module) {
                /** @var \Module $module */
                return $ask["module"] === $module->name;
            });
            foreach ($askModuleParameters as $ask) {
                if ($module->needphase === 'install') {
                    $answer = self::askParameter($ask, $helper, $input, $output);
                    $moduleMng->setParameterAnswer($ask["module"], $ask["name"], $answer);
                }
                if ($module->needphase === 'update') {
                    if (($ask["onupgrade"] ?? "") === "W") {
                        $answer = self::askParameter($ask, $helper, $input, $output);
                        $moduleMng->setParameterAnswer($ask["module"], $ask["name"], $answer);
                    }
                }
            }
        }
    }

    protected static function askParameter($ask, QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $predefinedValue = self::getParameter($ask["name"]);
        if ($predefinedValue !== null) {
            return $predefinedValue;
        }
        $output->getFormatter()->setStyle('qd', new OutputFormatterStyle('yellow'));
        $questionLabel = $ask["label"];
        if (!empty($ask["default"])) {
            $questionLabel .= sprintf(" <qd>[%s]</qd>", $ask["default"]);
        }
        $questionLabel .= "?";
        $questionLabel = sprintf('<question>%s </question>', $questionLabel);
        $availableAnswers = [];
        if (($ask["type"] ?? "") === "enum") {
            $availableAnswers = explode("|", $ask["values"]);
            $question = new ChoiceQuestion($questionLabel, $availableAnswers, $ask["default"] ?? "");
        } else {
            $question = new Question($questionLabel, $ask["default"] ?? "");
        }
        $question->setValidator(function ($answer) use ($ask, $availableAnswers) {
            if (!empty($ask["needed"]) && $ask["needed"] === "Y") {
                if (empty($answer)) {
                    throw new RuntimeException(
                        sprintf("Value for \"%s\" is required", $ask["name"])
                    );
                }
            }
            if ($availableAnswers && !in_array($answer, $availableAnswers)) {
                throw new \RuntimeException(
                    sprintf('Value must be one of "%s"', implode('", "', $availableAnswers))
                );
            }
            return $answer;
        });


        $response = $helper->ask($input, $output, $question);

        return $response;
    }


    protected static function getParameter($name)
    {
        if (!self::$askParameters) {
            $askFilePath = sprintf("%s/%s", Context::getControlPath(), self::askFile);
            if (is_file($askFilePath)) {
                self::$askParameters = json_decode(file_get_contents($askFilePath), true);
            }
        }

        return (self::$askParameters[$name] ?? null);
    }

    public static function setParameters(array $parameters)
    {
        $askFilePath = sprintf("%s/%s", Context::getControlPath(), self::askFile);
        if (!is_dir(sprintf("%s/%s", Context::getControlPath(), "conf"))) {
            if (mkdir(sprintf("%s/%s", Context::getControlPath(), "conf"), 0755) === false) {
                throw new \RuntimeException(sprintf("Error: could not create 'conf' directory"));
            }
        }
        if (file_put_contents($askFilePath, json_encode($parameters, JSON_PRETTY_PRINT)) === false) {
            throw new \RuntimeException(sprintf("Error: could not save content to '%s'", $askFilePath));
        }
    }

    public static function removeAskes()
    {
        $askFilePath = sprintf("%s/%s", Context::getControlPath(), self::askFile);
        if (is_file($askFilePath)) {
            unlink($askFilePath);
        }
    }
}
