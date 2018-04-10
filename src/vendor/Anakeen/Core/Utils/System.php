<?php

namespace Anakeen\Core\Utils;

use Anakeen\Core\ContextManager;

class System
{

    /**
     * increase limit if current limit is lesser than
     *
     * @param int $limit new limit in seconds
     */
    public static function setMaxExecutionTimeTo($limit)
    {
        $im = intval(ini_get("max_execution_time"));
        if ($im > 0 && $im < $limit && $limit >= 0) {
            ini_set("max_execution_time", $limit);
        }
        if ($limit <= 0) {
            ini_set("max_execution_time", 0);
        }
    }

    /**
     * send a message to system log
     *
     * @param string|string[] $msg message to log
     */
    public static function addLogMsg($msg)
    {
        $action = ContextManager::getCurrentAction();
        if ($action) {
            $action->parent->addLogMsg($msg);
        }
    }
    /**
     * record warning message to session
     *
     * @param string $msg message to log
     */
    public static function addWarningMsg($msg)
    {
        $action = ContextManager::getCurrentAction();
        if ($action) {
            $action->parent->addWarningMsg($msg);
        }
    }

    /**
     * exec list of unix command in background
     *
     * @param array $tcmd unix command strings
     * @param       $result
     * @param       $err
     */
    public static function bgExec($tcmd, &$result, &$err)
    {
        $foutname = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/bgexec");
        $fout = fopen($foutname, "w+");
        fwrite($fout, "#!/bin/bash\n");
        foreach ($tcmd as $v) {
            fwrite($fout, "$v\n");
        }
        fclose($fout);
        chmod($foutname, 0700);
        //  if (session_id()) session_write_close(); // necessary to close if not background cmd
        exec("exec nohup $foutname > /dev/null 2>&1 &", $result, $err);
        //if (session_id()) @session_start();
    }
}
