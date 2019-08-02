<?php


namespace Anakeen\SmartStructures\Task;

use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Exception;
use Anakeen\Script\ShellManager;
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
            $crontab = $this->getRawValue(TaskFields::task_crontab);
            if ($crontab) {
                // Normalize expression
                $cronExp = \Cron\CronExpression::factory($crontab);
                if ($cronExp->getExpression() !== $crontab) {
                    $this->setValue(TaskFields::task_crontab, $cronExp->getExpression());
                }
            }
        });
    }

    public static function checkCrontab($crontabExpression)
    {
        $error = CrontabManager::getCrontabError($crontabExpression);
        if ($error) {
            return $error;
        }
        return "";
    }

    public function execute()
    {
        if (!$this->canExecuteRoute()) {
            throw new Exception("Need edit privilege to execute task");
        } else {
            return $this->executeNow();
        }
    }

    /**
     * Change run date based on now
     * @throws Exception
     */
    public function updateRunDate()
    {
        $this->setValue(TaskFields::task_nextdate, $this->getNextExecDate());
        $this->modify();
    }

    protected function executeNow()
    {
        DbManager::savePoint("_taskExec");
        DbManager::lockPoint($this->initid);

        $this->select($this->getLatestId(false, true));
        $cmd = $this->getAnkCmd();
        $this->setValue(TaskFields::task_exec_state_result, "inprogress");
        $this->setValue(TaskFields::task_exec_date, Date::getNow());
        $this->modify();

        $d1 = new \DateTime();
        exec($cmd . " 2>&1", $output, $return);


        $d2 = new \DateTime();
        $diff = $d2->diff($d1);

        $this->setValue(TaskFields::task_exec_output, implode("\n", $output));
        $this->setValue(TaskFields::task_exec_duration, $diff->format("%H:%I:%S"));
        $this->setValue(TaskFields::task_exec_state_result, $return === 0 ? "success" : "fail");


        $this->revise();
        $this->clearValue(TaskFields::task_exec_output);
        $this->clearValue(TaskFields::task_exec_state_result);
        $this->clearValue(TaskFields::task_exec_duration);
        $this->clearValue(TaskFields::task_exec_date);
        $this->modify();

        DbManager::commitPoint("_taskExec");
        return $return;
    }

    protected function getAnkCmd()
    {
        $euid = $this->getRawValue(TaskFields::task_iduser);
        if (empty($euid)) {
            throw new \Anakeen\Core\Exception(sprintf("Task \"%s\": field \"%s\" is empty", $this->getTitle(), $this->getAttribute(TaskFields::task_iduser)->getLabel()));
        }
        $uid = AccountManager::getIdFromSEId($euid);
        $user = new Account("", $uid);


        $cmd = sprintf(
            '%s --route=%s --method=%s',
            ShellManager::getAnkCmd(false, $user->login),
            escapeshellarg($this->getRawValue(TaskFields::task_route_ns) . "::" . $this->getRawValue(TaskFields::task_route_name)),
            escapeshellarg($this->getRawValue(TaskFields::task_route_method))
        );


        $argsName = $this->getMultipleRawValues(TaskFields::task_arg_name);
        $argsVal = $this->getMultipleRawValues(TaskFields::task_arg_value);

        foreach ($argsName as $k => $argName) {
            if ($argName) {
                $cmd .= sprintf(
                    " %s=%s",
                    escapeshellarg('--arg-' . $argName),
                    escapeshellarg($argsVal[$k])
                );
            }
        }

        $queriesName = $this->getMultipleRawValues(TaskFields::task_queryfield_name);
        $queriesVal = $this->getMultipleRawValues(TaskFields::task_queryfield_value);
        $querySearch = '';
        foreach ($queriesName as $k => $queryName) {
            if ($queryName) {
                $querySearch = sprintf("&%s=%s", $queryName, $queriesVal[$k]);
            }
        }
        if ($querySearch) {
            $cmd .= sprintf(" --query=%s", escapeshellarg($querySearch));
        }

        return $cmd;
    }

    public function canExecuteRoute()
    {
        $err = $this->control('edit');
        return ($err == "") && !$this->isFixed();
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
            // Add One minute to be sure to change next run date after execution
            $oneMinuteAfter = new \DateTime();
            $oneMinuteAfter->add(new \DateInterval('PT1M'));
            return $cron->getNextRunDate($oneMinuteAfter)->format('Y-m-d H:i:s');
        }

        return false;
    }
}
