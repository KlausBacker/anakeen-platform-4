<?php


namespace Anakeen\SmartStructures\Task;

use Anakeen\Exception;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Task as TaskFields;

class TaskBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $this->setValue(TaskFields::task_nextdate, $this->getNextExecDate() ?: " ");
            $this->setValue(TaskFields::task_humancrontab, CrontabManager::getHumanSchedule($this->getRawValue(TaskFields::task_crontab)) ?: " ");
            $crontab= $this->getRawValue(TaskFields::task_crontab);
            if ($crontab) {
                // Normalize expression
                $cronExp=\Cron\CronExpression::factory($crontab);
                if ($cronExp->getExpression() !== $crontab) {
                    $this->setValue(TaskFields::task_crontab, $cronExp->getExpression());
                }
            }
        });
    }

    public static function checkCrontab($crontabExpression)
    {
        $error=CrontabManager::getCrontabError($crontabExpression);
        if ($error) {
            return $error;
        }
        return "";
    }

    /**
     * return the next date to execute process (false if not)
     *
     * @return string date|false
     */
    public function getNextExecDate()
    {
        $crontab = $this->getRawValue(TaskFields::task_crontab);
        if ($crontab) {
            try {
                $cron = \Cron\CronExpression::factory($crontab);
            } catch (\Exception $e) {
                throw new Exception("Invalid crontab :" . $e->getMessage());
            }

            return $cron->getNextRunDate()->format('Y-m-d H:i:s');
        }

        return false;
    }
}
