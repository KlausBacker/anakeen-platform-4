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
        $timerhourlimit = \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\Core\Settings::NsSde,
            "CORE_TIMERHOURLIMIT",
            2
        );
        if ((int) $timerhourlimit <= 0) {
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

    /**
     * get all actions need to be executed in the future
     * @param int $start = offset start with the element with "x" number
     * @param int $slice = max number of results
     * @param array $filters = array with all filter to execute
     * @return TimerTask[]
     */
    public static function getNextTaskToExecute(int $start = 0, int $slice = 0, array $filters = [])
    {
        $q = new \Anakeen\Core\Internal\QueryDb("", \DocTimer::class);
        $q->addQuery("donedate is null");
        $timerhourlimit = \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\Core\Settings::NsSde,
            "CORE_TIMERHOURLIMIT",
            2
        );
        if ((int) $timerhourlimit <= 0) {
            $timerhourlimit = 2;
        }
        $q->addQuery(sprintf("tododate > now() - interval '%d hour'", $timerhourlimit));
        foreach ($filters as $column => $value) {
            switch ($column) {
                case "title":
                    $q->addQuery(sprintf("title ~* '%s'", pg_escape_string($value)));
                    break;
            }
        }
        $q->order_by = "tododate asc, id";
        $l = $q->Query($start, $slice, "TABLE");
        if ($q->nb > 0) {
            $tasks = [];
            foreach ($l as $result) {
                $tasks[] = new TimerTask($result);
            }
            return $tasks;
        }
        return array();
    }

    /**
     * get all actions need to be executed in the future
     * @param int $start = offset start with the element with "x" number
     * @param int $slice = max number of results
     * @param array $filters = array with all filter to execute
     * @return TimerTask[]
     */
    public static function getPastTasks(int $start = 0, int $slice = 0, array $filters = [])
    {
        $q = new \Anakeen\Core\Internal\QueryDb("", \DocTimer::class);

        $timerhourlimit = \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\Core\Settings::NsSde,
            "CORE_TIMERHOURLIMIT",
            2
        );
        if ((int) $timerhourlimit <= 0) {
            $timerhourlimit = 2;
        }
        $q->addQuery(sprintf("donedate is not null or tododate < now() - interval '%d hour'", $timerhourlimit));
        foreach ($filters as $column => $value) {
            switch ($column) {
                case "title":
                    $q->addQuery(sprintf("title ~* '%s'", pg_escape_string($value)));
                    break;
            }
        }
        $q->order_by = "tododate asc, id";
        $l = $q->Query($start, $slice, "TABLE");
        if ($q->nb > 0) {
            $tasks = [];
            foreach ($l as $result) {
                $tasks[] = new TimerTask($result);
            }
            return $tasks;
        }
        return array();
    }

    /**
     * Change status
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    public static function detectNewExpired()
    {
        $timerhourlimit = \Anakeen\Core\ContextManager::getParameterValue(
            \Anakeen\Core\Settings::NsSde,
            "CORE_TIMERHOURLIMIT",
            2
        );
        if ((int) $timerhourlimit <= 0) {
            $timerhourlimit = 2;
        }

        $sql = sprintf(
            "update doctimer set donestatus = '%s' where (donestatus = '%s' or donestatus is null ) and donedate is null and tododate < now() - interval '%d hour' returning id",
            pg_escape_string(\DocTimer::expiredStatus),
            pg_escape_string(\DocTimer::waitingStatus),
            $timerhourlimit
        );

        DbManager::query($sql, $expired, true);
        return $expired;
    }
}
