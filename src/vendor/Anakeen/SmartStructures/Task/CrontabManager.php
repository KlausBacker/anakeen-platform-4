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
            $schedule = CronSchedule::fromCronString($crontabExpression);

            return $schedule->asNaturalLanguage();
        }
        return "";
    }

    public static function getCrontabError($crontabExpression)
    {
        try {
            \Cron\CronExpression::factory($crontabExpression);

            $minutes = self::getNextDates($crontabExpression, $number = 13, $format = "i");
            foreach ($minutes as $minute) {
                if (intval($minute) % 5 !== 0) {
                    return "Cron minutes period interval must be a multiple of 5";
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return "";
    }

    public static function getNextDates($crontabExpression, $number = 5, $format = "Y-m-d H:i:s")
    {
        $expression = \Cron\CronExpression::factory($crontabExpression);
        $dates = $expression->getMultipleRunDates($number);
        $data = [];
        /** @var \DateTime $date */
        foreach ($dates as $date) {
            $data[] = $date->format($format);
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
        return $dc;
    }
}
