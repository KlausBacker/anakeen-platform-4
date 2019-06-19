<?php

namespace Control\Cli;

use Control\Internal\ModuleManager;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AskParameters
{
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
                    $answer=self::askParameter($ask, $helper,$input, $output);
                    $moduleMng->setParameterAnswer($ask["module"], $ask["name"], $answer);
                }
                if ($module->needphase === 'update') {
                    if (($ask["onupgrade"] ?? "") === "W") {
                        $answer=self::askParameter($ask, $helper, $input, $output);
                        $moduleMng->setParameterAnswer($ask["module"], $ask["name"], $answer);
                    }
                }
            }
        }
    }

    protected static function askParameter($ask, QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
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
                    throw new \RuntimeException(
                        'This value is required'
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
}