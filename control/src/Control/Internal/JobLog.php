<?php


namespace Control\Internal;


use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

class JobLog
{
    protected static $jobData = null;
    /** @var ConsoleOutput */
    protected static $output = null;
    /** @var ConsoleSectionOutput[] */
    protected static $outputSection = null;

    protected static function setKey($moduleName, $phaseName, $key, $value, $adding = false)
    {
        $data = ModuleJob::getJobData();

        self::displayOutput($moduleName, $phaseName, $key, $value);

        list($usec) = explode(" ", microtime());
        $now = sprintf("%s.%s", date("Y-m-d\\TH:i:s"), substr($usec, 2, 6));
        if ($moduleName) {
            foreach ($data["tasks"] as &$task) {
                if ($task["module"] === $moduleName) {
                    if ($phaseName) {
                        foreach ($task["phases"] as &$phase) {
                            if ($phase["name"] === $phaseName) {
                                if ($adding === true) {
                                    $phase[$key][] = $value;
                                } elseif ($adding !== false) {
                                    $phase[$key][$adding] = $value;
                                    $phase[$key][$adding]["date"] = $now;
                                } else {
                                    $phase[$key] = $value;
                                }
                            }
                        }
                    } else {
                        $task[$key] = $value;
                        $task["date"] = $now;
                    }
                }
            }
        } else {
            if ($key !== "log") {
                $data[$key] = $value;
            }
        }
        
            $msg = ["module" => $moduleName, "phase" => $phaseName, "task" => $key, "value" => $value, "date" => $now];
            $data["log"][] = $msg;

        ModuleJob::putJobData($data);
    }


    protected static function getKey($moduleName, $phaseName, $key, $index = -1)
    {
        $data = ModuleJob::getJobData();

        if ($moduleName) {
            foreach ($data["tasks"] as &$task) {
                if ($task["module"] === $moduleName) {
                    if ($phaseName) {
                        foreach ($task["phases"] as &$phase) {
                            if ($phase["name"] === $phaseName) {
                                if ($index !== -1) {
                                    return $phase[$key][$index]["status"] ?? null;
                                } else {
                                    return $phase[$key] ?? null;
                                }
                            }
                        }
                    } else {
                        return $task[$key] ?? null;
                    }
                }
            }
        } else {
            return $data[$key] ?? null;
        }
        return null;
    }

    public static function displayOutput($moduleName, $phaseName, $key, $value)
    {
        if (self::$output) {
            if (is_array($value)) {
                $msg = implode(", ", $value);
            } else {
                $msg = $value;
            }
            $msg = sprintf("\r<info>%s</info> : %s  : %s : <comment>%s</comment>", $moduleName, $phaseName, $key, $msg);

            if ($moduleName) {
                if (!isset(self::$outputSection[$moduleName])) {
                    self::$outputSection[$moduleName] = self::$output->section();
                }

                if (!self::$output->isVeryVerbose()) {
                    self::$outputSection[$moduleName]->clear();
                }
                self::$outputSection[$moduleName]->writeln($msg, OutputInterface::VERBOSITY_VERBOSE);
            } else {
                self::$output->writeln($msg, OutputInterface::VERBOSITY_VERBOSE);
            }
        }
    }

    public static function writeInterruption($status = ModuleJob::INTERRUPTED_STATUS)
    {
        $data = ModuleJob::getJobData();

        $data["status"] = $status;
        if (isset($data["tasks"])) {
            foreach ($data["tasks"] as &$task) {
                if ($task["status"] === ModuleJob::RUNNING_STATUS) {
                    $task["status"] = $status;
                }

                foreach ($task["phases"] as &$phase) {
                    if ($phase["status"] === ModuleJob::RUNNING_STATUS) {
                        $phase["status"] = $status;
                    }
                    if (!empty($phase["process"])) {
                        foreach ($phase["process"] as &$process) {
                            if ($process["status"] === ModuleJob::RUNNING_STATUS) {
                                $process["status"] = $status;
                            }
                        }
                    }
                }
            }
        }

        ModuleJob::putJobData($data);
        self::addLog("", "", "Interrupted process");
    }


    public static function markProcessFailedAsIgnored()
    {
        $data = ModuleJob::getJobData();

        foreach ($data["tasks"] as &$task) {
            foreach ($task["phases"] as &$phase) {
                if (!empty($phase["process"])) {
                    foreach ($phase["process"] as &$process) {
                        if ($process["status"] === ModuleJob::FAILED_STATUS) {
                            $process["status"] = "IGNORED";
                        }
                    }
                }
            }
        }

        ModuleJob::putJobData($data);
    }


    public static function setStatus($moduleName, $phaseName, $status)
    {
        self::setKey($moduleName, $phaseName, "status", $status);
    }

    public static function getStatus($moduleName, $phaseName)
    {
        return self::getKey($moduleName, $phaseName, "status");
    }

    public static function getProcessStatus($moduleName, $phaseName, $index)
    {
        return self::getKey($moduleName, $phaseName, "process", $index);
    }

    public static function setInfo($moduleName, $key, $value)
    {
        self::setKey($moduleName, "", $key, $value);
    }

    public static function setError($moduleName, $phaseName, $status)
    {
        self::setKey($moduleName, $phaseName, "error", $status);
    }

    public static function setWarning($moduleName, $phaseName, $status)
    {
        self::setKey($moduleName, $phaseName, "warning", $status);
    }

    public static function addLog($moduleName, $phaseName, $msg)
    {
        self::setKey($moduleName, $phaseName, "log", $msg, true);
    }

    public static function setProcess($moduleName, $phaseName, $msg, $index)
    {
        self::setKey($moduleName, $phaseName, "process", $msg, $index);
    }

    public static function setOutput(ConsoleOutput $output)
    {
        self::$output = $output;
    }

    public static function clearLog()
    {
        self::setKey("", "", "log", []);
    }


}