<?php

namespace Anakeen\Core\Cron;

use SmartStructure\Fields\Task as TaskFields;

class ProcessExecuteAPI
{
    public static $debug = false;


    protected static function lock()
    {
        self::debug(sprintf("Locking exclusive execution..."));
        $i1 = unpack("i", "PROC") [1];
        $i2 = unpack("i", "EXEC") [1];
        \Anakeen\Core\DbManager::query(sprintf("SELECT pg_try_advisory_lock(%d, %d)", $i1, $i2), $res, true, true);
        if ($res !== 't') {
            $msg = sprintf("A 'processExecute' API script is already running.");
            self::debug($msg);
            throw new processExecuteAPIAlreadyRunningException($msg);
        }
        self::debug(sprintf("Exclusive execution locked."));
        return $res;
    }

    protected static function unlock($lock)
    {
        /* Unlock will be performed when the process exits and the Postgres connection is torn down. */
    }

    public static function debug($msg)
    {
        if (self::$debug) {
            error_log($msg);
        }
    }

    public static function executeAll()
    {
        $lock = self::lock();
        try {
            self::verifyTaskElements();
            self::verifyRecordedTimers();
        } catch (\Exception $e) {
            self::unlock($lock);
            throw $e;
        }
        self::unlock($lock);
    }

    public static function executeSingleTimer($doctimerId)
    {
        $dt = new \DocTimer("", $doctimerId);
        $time_start = microtime(true);
        $err = $dt->executeTimerNow();
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if ($err) {
            \Anakeen\LogManager::error(sprintf("Error while executing timer %s (%d): %s in %.03f seconds", $dt->title, $dt->id, $err, $time));
            print sprintf("Error while executing timer %s (%d): %s in %.03f seconds", $dt->title, $dt->id, $err, $time);
        } else {
            \Anakeen\LogManager::info(sprintf("Timer %s (%d) executed in %.03f seconds", $dt->title, $dt->id, $time));
            print sprintf("Timer %s (%d) executed in %.03f seconds", $dt->title, $dt->id, $time);
        }
    }

    /**
     * @param \SmartStructure\Task|string $exec
     */
    public static function executeSingleTask($exec)
    {
        if (is_scalar($exec)) {
            /**
             * @var \SmartStructure\Task $exec
             */
            $exec = \Anakeen\Core\SEManager::getDocument($exec);
        }
        if (!is_object($exec) || !is_a($exec, \SmartStructure\Task::class) || !$exec->isAlive()) {
            return;
        }
        $exec->execute();
    }

    public static function verifyTaskElements()
    {
        // Verify Task document
        $now = \Anakeen\Core\Internal\SmartElement::getTimeDate();

        $s = new \SearchDoc("", "TASK");
        $s->setObjectReturn();
        $s->addFilter(sprintf("%s < %s", TaskFields::task_nextdate, pg_escape_literal($now)));
        $s->addFilter("%s = 'active'", TaskFields::task_status);
        //  $s->setDebugMode();
        $s->search();

        while ($de = $s->getNextDoc()) {
            $de->setValue("exec_status", "waiting");
            $de->modify(true, array(
                "exec_status"
            ), true);
        }

        $s = new \SearchDoc("", "TASK");
        $s->setObjectReturn();
        $s->addFilter(sprintf("exec_nextdate < %s", pg_escape_literal($now)));
        $s->addFilter("exec_status != 'progressing'");
        //$s->setDebugMode();
        $s->search();
        //print_r2($s->getDebugInfo());
        self::debug(__METHOD__ . " " . sprintf("Found %d tasks to execute.", $s->count()));
        if ($s->count() <= 0) {
            return;
        }

        while ($de = $s->getNextDoc()) {
            /**
             * @var \SmartStructure\Task $de
             */
            self::debug(__METHOD__ . " " . sprintf("Executing task '%s' (%d).", $de->getTitle(), $de->id));
            self::executeSingleTask($de);
        }
        unset($exec);
        return;
    }

    public static function verifyRecordedTimers()
    {
        $ate = \Anakeen\Core\TimerManager::getTaskToExecute();

        self::debug(__METHOD__ . " " . sprintf("Found %d doctimers.", count($ate)));
        foreach ($ate as $task) {
            try {
                $tmpfile = tempnam(\Anakeen\Core\ContextManager::getTmpDir(), __METHOD__);
                if ($tmpfile === false) {
                    throw new \Exception("Error: could not create temporary file.");
                }
                $cmd = sprintf("%s/ank.php --script=processExecute --doctimer-id=%s > %s 2>&1", DEFAULT_PUBDIR, escapeshellarg($task->id), escapeshellarg($tmpfile));
                self::debug(__METHOD__ . " " . sprintf("Running '%s'", $cmd));
                system($cmd, $ret);
                $out = file_get_contents($tmpfile);
                unlink($tmpfile);
                if ($ret !== 0) {
                    throw new \Exception(sprintf("Process '%s' returned with error (%d): %s", $cmd, $ret, $out));
                }
            } catch (\Exception $e) {
                $errMsg = \Anakeen\Core\LogException::formatErrorLogException($e);

                error_log($errMsg);
                if (!\Anakeen\Script\ShellManager::isInteractiveCLI()) {
                    $expand = array(
                        'm' => preg_replace('/^([^\n]*).*/s', '\1', $e->getMessage())
                    );
                    \Anakeen\Script\ShellManager::sendEmailError($errMsg, $expand);
                }
            }
        }
    }
}
