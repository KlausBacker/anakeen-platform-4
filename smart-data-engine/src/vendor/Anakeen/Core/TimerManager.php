<?php

namespace Anakeen\Core;

class TimerManager
{
    /**
     * @param Internal\SmartElement $elt
     * @return TimerTask[]
     * @throws \Anakeen\Database\Exception
     */
    public static function getElementTasks(\Anakeen\Core\Internal\SmartElement $elt)
    {
        $sql = sprintf("select * from doctimer where docid=%d order by tododate", $elt->id);
        DbManager::query($sql, $results);
        $tasks = [];
        foreach ($results as $result) {
            $tasks[] = new TimerTask($result);
        }
        return $tasks;
    }
    /**
     * get all actions need to be executed now
     * @return TimerTask[]
     */
    public static function getTaskToExecute()
    {
        $q = new \Anakeen\Core\Internal\QueryDb("", \DocTimer::class);
        $q->addQuery("donedate is null");
        $q->addQuery("tododate < now()");
        $timerhourlimit = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_TIMERHOURLIMIT", 2);
        if ((int)$timerhourlimit <= 0) {
            $timerhourlimit = 2;
        }
        $q->addQuery(sprintf("tododate > now() - interval '%d hour'", $timerhourlimit));
        $l = $q->Query(0, 0, "TABLE");
        if ($q->nb > 0) {
            $tasks = [];
            foreach ($l as $result) {
                $tasks[] = new TimerTask($result);
            }
            return $tasks;
        }
        return array();
    }
}
