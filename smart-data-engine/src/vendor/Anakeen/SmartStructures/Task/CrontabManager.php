<?php


namespace Anakeen\SmartStructures\Task;

use Anakeen\Core\ContextManager;

class CrontabManager
{
    public static function getCurrentSmartUserId()
    {
        return ContextManager::getCurrentUser()->fid;
    }

    public static function getHumanSchedule($crontabExpression)
    {
        if ($crontabExpression) {
            $language = ContextManager::getLanguage() === "fr_FR" ? "fr" : "en";
            $schedule = CronSchedule::fromCronString($crontabExpression, $language);

            return $schedule->asNaturalLanguage();
        }
        return "";
    }

    public static function getCrontabError($crontabExpression)
    {
        try {
            \Cron\CronExpression::factory($crontabExpression);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return "";
    }

    public static function getNextDates($crontabExpression, $number = 5, $format = "%Y-%m-%d, %H:%M")
    {
        $expression = \Cron\CronExpression::factory($crontabExpression);
        $dates = $expression->getMultipleRunDates($number);
        $data = [];
        /** @var \DateTime $date */
        foreach ($dates as $date) {
            $data[] = strftime($format, $date->getTimestamp());
        }
        return $data;
    }

    public static function getCrontabParts($crontabExpression)
    {
        $d = preg_split('/\s/', $crontabExpression, -1, PREG_SPLIT_NO_EMPTY);

        $dc["minutes"] = $d[0];
        $dc["hours"] = $d[1];
        $dc["days"] = $d[2];
        $dc["months"] = $d[3];
        $dc["weekDays"] = $d[4];
        $dc["minutesTranslation"] = ___("minutes", "TASK");
        $dc["hoursTranslation"] = ___("hours", "TASK");
        $dc["daysTranslation"] = ___("days", "TASK");
        $dc["monthsTranslation"] = ___("months", "TASK");
        $dc["weekDaysTranslation"] = ___("weekdays", "TASK");
        return $dc;
    }
}
