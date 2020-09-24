<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\DbManager;

class StatLogConnectionManager
{
    /**
     * Get number of connections by month
     * @param string $fromDate YYYY-MM-DD (if empty from beginning)
     * @param string $toDate YYYY-MM-DD (if empty to the end)
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    public static function getMonthsLogin(&$fromDate = "", &$toDate = "")
    {
        $where=self::getMonthsSqlWhere($fromDate, $toDate);

        $sql = sprintf(
            "select distinct login from logmonthconnection where %s order by  login",
            $where
        );

        DbManager::query($sql, $results, true);

        return $results;
    }
    /**
     * Get number of connections by month
     * @param string $fromDate YYYY-MM-DD (if empty from beginning) (return first day of month)
     * @param string $toDate YYYY-MM-DD (if empty return today) (return last day of month)
     * @return int
     * @throws \Anakeen\Database\Exception
     */
    public static function getMonthsCount(&$fromDate = "", &$toDate = "")
    {
        $where=self::getMonthsSqlWhere($fromDate, $toDate);

        $sql = sprintf(
            "select count(distinct login) from logmonthconnection where %s",
            $where
        );

        DbManager::query($sql, $results, true, true);

        return intval($results);
    }

    /**
     * Compute sql where part and adjust date to first day and last day of month
     * @param string $fromDate (return first day of month)
     * @param string $toDate (return last day of month)
     * @return string
     * @throws \Anakeen\Database\Exception
     */
    protected static function getMonthsSqlWhere(&$fromDate = "", &$toDate = "")
    {
        $fromWhere = "true";
        $toWhere = "true";

        if ($fromDate) {
            $time = strtotime($fromDate);
            $fromDate = date("Y-m-01", $time);
            $fromWhere = sprintf("monthdate >= '%s'", pg_escape_string($fromDate));
        } else {
            DbManager::query("select min(monthdate) from logmonthconnection", $fromDate, true, true);
        }
        if ($toDate) {
            $time = strtotime($toDate);
            $toDate = date("Y-m-t", $time);
            $toWhere = sprintf("monthdate <= '%s'", pg_escape_string($toDate));
        } else {
            $toDate = date("Y-m-d");
        }

        return sprintf("%s and %s", $fromWhere, $toWhere);
    }

    /**
     * return login connected during the given month
     * return number of daily connection for each login
     * @param string $monthDate YYYY-MM-DD
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    public static function getLoginMonthStats(string $monthDate)
    {
        $time = strtotime($monthDate);
        $start = date("Y-m-01", $time);
        $end = date("Y-m-t", $time);

        $sql = sprintf(
            "select  login, count(login) as counter
                 from logconnection 
                 where startdate >= '%s' and startdate <= '%s' and enddate = startdate group by login order by login",
            $start,
            $end
        );
        DbManager::query($sql, $results);
        $stats = [];
        foreach ($results as $result) {
            $stats[$result["login"]] = $result["counter"];
        }
        return $stats;
    }
}
