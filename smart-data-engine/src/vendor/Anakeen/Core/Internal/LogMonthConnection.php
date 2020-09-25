<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;

class LogMonthConnection extends DbObj
{
    public $fields = array(
        'monthdate',
        'login'
    );

    public $monthdate;
    public $login;

    public $id_fields = array(
        'monthdate',
        'login',
    );

    public $dbtable = 'logmonthconnection';

    public $sqlcreate = "
    CREATE TABLE logmonthconnection (
      monthdate date,
      login text
    );
    create unique index on logmonthconnection (monthdate , login);
  ";

    /**
     * @param string $login
     * @param string $date YYYY-MM-DD
     * @throws \Anakeen\Core\Exception
     */
    public static function addLog(string $login, string $date = "")
    {
        if ($date === "") {
            $date = date("Y-m-01");
        }

        // Use session var to increase speed to log connection date
        if (ContextManager::getSession()->read("loggedmonthdate") !== $date) {
            $log = new LogMonthConnection("", [$date, $login]);
            if (!$log->isAffected()) {
                $log->monthdate = $date;
                $log->login = $login;
                $log->add();
            }
            ContextManager::getSession()->register("loggedmonthdate", $date);
        }
    }
}
