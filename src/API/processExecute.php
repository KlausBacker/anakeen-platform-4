<?php
/**
 *  Execute Schedule Processes (task and timers) when needed
 *
 */

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Execute Processes when needed");
$doctimerId = $usage->addOptionalParameter('doctimer-id', 'Doctimer identifier', null, null);
$taskId = $usage->addOptionalParameter('task-id', 'Exec identifier', null, null);
\Anakeen\Core\Cron\ProcessExecuteAPI::$debug = ($usage->addEmptyParameter('debug', 'Enable debugging verbose output') !== false);
$usage->verify();

if ($doctimerId !== null && $taskId !== null) {
    throw new \Anakeen\Core\Cron\ProcessExecuteAPIException("Error: only one of '--doctimer-id' or '--exec-id'' should be used.\n");
}

if ($doctimerId !== null) {
    \Anakeen\Core\Cron\ProcessExecuteAPI::executeSingleTimer($doctimerId);
} elseif ($taskId !== null) {
    \Anakeen\Core\Cron\ProcessExecuteAPI::executeSingleTask($taskId);
} else {
    try {
        \Anakeen\Core\Cron\ProcessExecuteAPI::executeAll();
    } catch (\Anakeen\Core\Cron\ProcessExecuteAPIAlreadyRunningException $e) {
        /* Skip execution and silently ignore already running processes */
    }
}
