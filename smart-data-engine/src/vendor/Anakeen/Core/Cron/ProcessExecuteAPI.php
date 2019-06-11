<?php

namespace Anakeen\Core\Cron;

use Anakeen\Core\Utils\System;
use Anakeen\Exception;
use Anakeen\Script\ShellManager;
use Anakeen\SmartStructures\Task\TaskManager;

class ProcessExecuteAPI
{
    public static $debug = false;
    /** @var \Exception[] */
    protected static $recordError=[];


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
            self::bgExecTaskElements();
            self::bgExecRecordedTimers();
            if (self::$recordError) {
                $msg=[];
                foreach (self::$recordError as $e) {
                    $msg[]=$e->getMessage();
                }
                throw new Exception(implode("\n", $msg));
            }
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

    protected static function bgExecTaskElements()
    {
        // Verify Task elements

        $tasks = TaskManager::getTaskToExecute();
        self::debug(sprintf("%d task detected.", $tasks->count()));
        foreach ($tasks as $task) {
            /**
             * @var \SmartStructure\Task $task
             */
            try {
                self::bgExecuteProcess(["task-id" => $task->id]);
                $task->updateRunDate();
            } catch (\Exception $e) {
                self::$recordError[]=$e;
            }
        }
    }

    protected static function bgExecRecordedTimers()
    {
        $timerTasks = \Anakeen\Core\TimerManager::getTaskToExecute();

        self::debug(sprintf("%d timer detected.", count($timerTasks)));
        foreach ($timerTasks as $task) {
            try {
                self::bgExecuteProcess(["timer-id" => $task->id]);
            } catch (\Exception $e) {
                self::$recordError[]=$e;
            }
        }
    }



    protected static function bgExecuteProcess(array $args)
    {
        $tmpfile = tempnam(\Anakeen\Core\ContextManager::getTmpDir(), "bgExec");
        if ($tmpfile === false) {
            throw new \Anakeen\Exception("Error: could not create temporary file.");
        }
        $sarg = '';
        foreach ($args as $k => $arg) {
            $sarg .= sprintf(" --%s=%s", $k, escapeshellarg($arg));
        }
        $cmd = sprintf(
            "%s --script=processExecute %s > %s 2>&1",
            ShellManager::getAnkCmd(),
            $sarg,
            escapeshellarg($tmpfile)
        );

        print "> $cmd\n";
        System::bgExec([$cmd]);
    }
}
