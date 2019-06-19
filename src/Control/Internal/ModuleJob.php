<?php


namespace Control\Internal;


use Symfony\Component\Console\Exception\RuntimeException;

require_once __DIR__ . "/../../../include/lib/Lib.Cli.php";

class ModuleJob
{

    protected static $jobData = null;
    protected static $processIndex = 0;

    protected static function getRunDir()
    {
        $rundir = realpath(__DIR__ . "/../../..") . "/run";
        if (!is_dir($rundir)) {
            if (!mkdir($rundir)) {
                throw new RuntimeException(sprintf("Cannot create directory \"%s\".", $rundir));
            }
        }
        return $rundir;
    }

    protected static function getJobFile()
    {
        return sprintf("%s/job.json", self::getRunDir());
    }

    public static function getJobData()
    {
        $file = self::getJobFile();
        $content = file_get_contents($file);
        if (!$content) {
            throw new RuntimeException("No job file : \"%s\"", $file);
        }

        return json_decode($content, true);
    }


    public static function putJobData($data)
    {
        $file = self::getJobFile();
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected static function archiveJobFile()
    {
        $file = self::getJobFile();
        $jobDir = sprintf("%s/log", self::getRunDir());
        if (!is_dir($jobDir)) {
            mkdir($jobDir);
        }
        $archiveName = sprintf("%s/job-%s.json", $jobDir, date('Ymd-His'));
        rename($file, $archiveName);
    }

    protected static function getPidFile()
    {
        return sprintf("%s/pid", self::getRunDir());
    }

    public static function initJobTask(ModuleManager $module, $options = [])
    {
        if (self::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }
        $data["action"] = $module->getMainPhase();
        $data["moduleArg"] = $module->getName();
        $data["options"] = $options;
        $dependencies = $module->getDepencies();
        foreach ($dependencies as $dependency) {
            $task = [
                "module" => $dependency->name,
                "action" => $dependency->needphase,
                "status" => "TODO"
            ];
            $task["phases"][] = ["name" => "download", "status" => "TODO"];
            foreach ($dependency->getPhaseList($dependency->needphase) as $phase) {
                $task["phases"][] = ["name" => $phase, "status" => "TODO"];
            }
            $data["tasks"][] = $task;
        }

        $data["parameters"] = $module->getAllParameters();
        $jobFile = self::getJobFile();
        if (!file_put_contents($jobFile, json_encode($data, JSON_PRETTY_PRINT))) {
            throw new RuntimeException(sprintf("Cannot write job file \"%s\"", $jobFile));
        }
    }


    public static function isRunning()
    {
        $pidFile = self::getPidFile();
        return file_exists($pidFile);
    }

    public static function hasFailed()
    {
        if (self::isRunning()) {
            return null;
        }
        $jobFile = self::getJobFile();
        return file_exists($jobFile);
    }

    public static function runJob()
    {
        $pidFile = "";
        if (self::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }
        try {
            $pidFile = self::getPidFile();
            self::catchExit();
            file_put_contents($pidFile, posix_getpid());
            $jobFile = self::getJobFile();
            if (!file_exists($jobFile)) {
                throw new RuntimeException(sprintf("No job file \"%s\"", $jobFile));
            }
            self::$jobData = json_decode(file_get_contents($jobFile), true);
            if (!self::$jobData) {
                throw new RuntimeException(sprintf("Unreadable job file \"%s\"", $jobFile));
            }
            self::dotheJob();
        } catch (\Exception $e) {
            if ($pidFile && file_exists($pidFile)) {
                unlink($pidFile);
            }

            JobLog::setStatus("", "", "exception");
            JobLog::setError("", "", $e->getMessage());
            throw $e;
        }
        unlink($pidFile);
    }

    protected static function catchExit()
    {
        set_error_handler(function ($error, $msg) {
            JobLog::setStatus("", "", "exception");
            JobLog::setError("", "", $msg);
            return false;
        });
        $pidFile = self::getPidFile();
        register_shutdown_function(function () use ($pidFile) {
            if ($pidFile && file_exists($pidFile)) {
                unlink($pidFile);
            }
        });
        declare(ticks=1);

        $signalHandler = function () use ($pidFile) {
            JobLog::setStatus("", "", "interrupted");
            JobLog::writeInterruption();
            if ($pidFile && file_exists($pidFile)) {
                unlink($pidFile);
            }
            exit();
        };
        // Catch Ctrl-C signal
        pcntl_signal(SIGINT, $signalHandler);
        // Catch Normal KILL
        pcntl_signal(SIGTERM, $signalHandler);
    }

    protected static function dotheJob()
    {
        $moduleName = self::$jobData["moduleArg"];
        if ($moduleName) {
            $module = new ModuleManager($moduleName);
        } else {
            $module = new ModuleManager("");
        }
        switch (self::$jobData["action"]) {
            case "install":
                $module->prepareInstall();
                break;
            case "upgrade":
                $module->prepareUpgrade(true);
                break;
        }
        if (self::installDependencies($module)) {
            JobLog::setStatus("", "", "done");
            // Job succeeded
            self::archiveJobFile();
        }

    }

    /**
     * @param ModuleManager $moduleManager
     * @param array         $options
     *
     * @return bool
     * @throws \Exception
     */
    public static function installDependencies(ModuleManager $moduleManager, $options = [])
    {
        $context = Context::getContext();
        $depList = $moduleManager->getDepencies();

        if (!$depList) {
            return true;
        }
        $downloaded = array();
        foreach ($depList as $module) {
            /**
             * @var \Module $module
             */
            $type = "";
            if ($module->needphase) {
                if ($module->needphase == 'replaced') {
                    $type = 'unregister';
                } else {
                    $type = $module->needphase;
                }
            }

            if (!$type) {
                throw new RuntimeException(sprintf("Module %s has no phase", $module->name));
            }


            JobLog::setInfo($module->name, "version", $module->version);
            JobLog::setInfo($module->name, "processing", $module->needphase);
            JobLog::setInfo($module->name, "status", "RUNNING");

            if ($module->needphase == 'replaced') {
                /**
                 * Unregister module
                 */
                $mod = $context->getModuleInstalled($module->name);
                if ($mod === false) {
                    continue;
                }
                echo sprintf("Unregistering module '%s'.\n", $module->name);
                $ret = $context->removeModule($module->name);
                if ($ret === false) {
                    printerr(sprintf("Error: could not unregister module '%s' from context: %s\n", $module->name, $context->errorMessage));
                    return 1;
                }
                $ret = $context->deleteFilesFromModule($module->name);
                if ($ret === false) {
                    printerr(sprintf("Error: could not delete files for module '%s': %s\n", $module->name, $context->errorMessage));
                    return 1;
                }
                $ret = $context->deleteManifestForModule($module->name);
                if ($ret === false) {
                    printerr(sprintf("Error: could not delete manifest file for module '%s': %s\n", $module->name, $context->errorMessage));
                    return 1;
                }

                continue;
            }

            if ($module->status == 'downloaded' && is_file($module->tmpfile)) {
                JobLog::setStatus($module->name, "download", "DONE");
                JobLog::setInfo($module->name, "downloadFile", $module->tmpfile);
            } else {
                if (JobLog::getStatus($module->name, "download") !== "DONE") {
                    JobLog::setStatus($module->name, "download", "RUNNING");
                    /**
                     * download module
                     */
                    $ret = $module->download('downloaded');
                    if ($ret === false) {
                        JobLog::setError($module->name, "download", $module->errorMessage);
                        throw new RuntimeException($module->errorMessage);
                    }
                    if (!empty($module->warningMessage)) {

                        JobLog::setWarning($module->name, "download", $module->warningMessage);

                    }

                    JobLog::setStatus($module->name, "download", "DONE");
                    JobLog::setInfo($module->name, "downloadFile", $module->tmpfile);
                } else {
                    JobLog::displayOutput($module->name, "download", "status", "<warning>SKIP</warning>");
                }
            }
            /**
             * switch to the module object from the context XML database
             */
            $modName = $module->name;
            $module = $context->getModuleDownloaded($modName);
            if ($module === false) {
                JobLog::setError($module->name, "download", $context->errorMessage);
                throw new RuntimeException($context->errorMessage);
            }

            /**
             * wstop
             */
            $context->wstop();
            /**
             * ask module parameters
             */
            $paramList = $module->getParameterList();
            if ($paramList !== false && count($paramList) > 0) {
                foreach ($paramList as $param) {
                    /**
                     * @var \Parameter $param
                     */
                    $visibility = $param->getVisibility($type);
                    if ($visibility != 'W') {
                        continue;
                    }

                    $pvalue = $param->value == "" ? $param->default : $param->value;

                    $value = self::getParameterAnswer($module->name, $param->name);

                    if ($value === false) {
                        throw new RuntimeException(sprintf("Error: could not read answer for \"%s\"!", $param->name));
                    }
                    $param->value = $param::cleanXMLUTF8($value);

                    $ret = $module->storeParameter($param);
                    if ($ret === false) {
                        printerr(sprintf("Error: could not store parameter '%s'!\n", $param->name));
                        return false;
                    }

                    echo "\n";
                }
            }
            /**
             * Execute phase/process list
             */
            $phaseList = $module->getPhaseList($type);

            if (!empty(self::$jobData["options"]["nothing"])) {
                $phaseList = array_filter($phaseList, function ($v) {
                    return !preg_match("/^(pre|post)-/", $v);
                });
            }
            if (!empty(self::$jobData["options"]["nopre"])) {

                $phaseList = array_filter($phaseList, function ($v) {
                    return !preg_match("/^pre-/", $v);
                });
            }
            if (!empty(self::$jobData["options"]["nopost"])) {
                $phaseList = array_filter($phaseList, function ($v) {
                    return !preg_match("/^post-/", $v);
                });
            }

            foreach ($phaseList as $phaseName) {
                switch ($phaseName) {
                    case 'clean-unpack':
                        if (JobLog::getStatus($module->name, $phaseName) !== "DONE") {
                            JobLog::setStatus($module->name, $phaseName, "RUNNING");
                            $ret = $context->deleteFilesFromModule($module->name);
                            if ($ret === false) {
                                JobLog::setError($module->name, $phaseName,
                                    sprintf("Error: could not delete old files for module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                                return false;
                            }
                            $ret = $module->unpack($context->root);
                            if ($ret === false) {
                                JobLog::setError($module->name, $phaseName,
                                    sprintf("Error: could not unpack module '%s' in '%s': %s\n", $module->name, $context->root, $module->errorMessage));
                                return false;
                            }
                            JobLog::setStatus($module->name, $phaseName, "DONE");
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    case 'unpack':
                        if (JobLog::getStatus($module->name, $phaseName) !== "DONE") {
                            JobLog::setStatus($module->name, $phaseName, "RUNNING");
                            $ret = $module->unpack($context->root);
                            if ($ret === false) {
                                JobLog::setError($module->name, $phaseName,
                                    sprintf("Error: could not unpack module '%s' in '%s': %s\n", $module->name, $context->root, $module->errorMessage));
                                return false;
                            }
                            JobLog::setStatus($module->name, "unpack", "DONE");
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    case 'unregister-module':

                        if (JobLog::getStatus($module->name, $phaseName) !== "DONE") {
                            JobLog::setStatus($module->name, $phaseName, "RUNNING");
                            $ret = $context->removeModule($module->name);
                            if ($ret === false) {
                                JobLog::setError($module->name, $phaseName,
                                    sprintf("Error: could not remove module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                                return false;
                            }
                            $ret = $context->deleteFilesFromModule($module->name);
                            if ($ret === false) {
                                JobLog::setError($module->name, $phaseName,
                                    sprintf("Error: could not delete files for module '%s' in '%s': %s\n", $module->name, $context->root, $context->errorMessage));
                                return false;
                            }
                            $ret = $context->deleteManifestForModule($module->name);
                            if ($ret === false) {

                                JobLog::setError($module->name, $phaseName, sprintf("Error: could not delete manifest"));
                                return false;
                            }

                            JobLog::setStatus($module->name, $phaseName, "DONE");
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    case 'purge-unreferenced-parameters-value':
                        if (JobLog::getStatus($module->name, $phaseName) !== "DONE") {
                            JobLog::setStatus($module->name, $phaseName, "RUNNING");
                            $ret = $context->purgeUnreferencedParametersValue();
                            if ($ret === false) {
                                JobLog::setError($module->name, $phaseName,
                                    sprintf("Error: could not purge unreferenced parameters value in '%s': %s\n", $context->root, $context->errorMessage));
                                return false;
                            }
                            JobLog::setStatus($module->name, $phaseName, "DONE");
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    default:
                        if (($ret = self::executeModulePhase($module, $phaseName)) != 0) {
                            return false;
                        }
                        break;
                }
            }
            /**
             * set status to 'installed'
             */
            if ($type === 'upgrade') {
                $ret = $context->removeModuleInstalled($module->name);
                if ($ret === false) {
                    JobLog::setError($module->name, "", sprintf("Error: Could not remove old installed module '%s': %s\n", $module->name, $context->errorMessage));

                    return false;
                }
            }
            $ret = $module->setStatus('installed');
            if ($ret === false) {
                JobLog::setError($module->name, "", sprintf("Error: Could not set installed status on module '%s': %s\n", $module->name, $module->errorMessage));
                return false;
            }
            $module->cleanupDownload();


            JobLog::setInfo($module->name, "status", "INSTALLED");
            /**
             * wstart
             */
            $ret = $context->wstart();
            if ($ret !== 0) {
                JobLog::setError($module->name, "", "Error restarting Anakeen Platform");
            }

            array_push($downloaded, $module);
        }


        return true;
    }

    protected static function executeModulePhase(\Module $module, $phaseName)
    {
        $phase = $module->getPhase($phaseName);
        $processList = $phase->getProcessList();
        $ret = true;
        if (JobLog::getStatus($module->name, $phase->name) !== "DONE") {
            JobLog::setStatus($module->name, $phase->name, "RUNNING");
            $ret = self::executeProcessList($processList);
            if ($ret !== true) {
                JobLog::setStatus($module->name, $phase->name, "FAILED");
                JobLog::writeInterruption("FAILED");
            } else {
                JobLog::setStatus($module->name, $phase->name, "DONE");
            }
        } else {
            JobLog::displayOutput($module->name, $phase->name, "status", "<warning>SKIP</warning>");
        }
        return ($ret === true) ? 0 : 1;
    }

    /**
     * @param \Process[] $processList
     *
     * @return int
     */
    protected static function executeProcessList($processList)
    {
        foreach ($processList as & $process) {

            self::$processIndex++;
            $command = $process->getAttribute("command");
            if ($command) {
                $index = hash("md5", $command);
            } else {
                $index = self::$processIndex;
            }
            $configStatus = JobLog::getProcessStatus($process->phase->module->name, $process->phase->name, $index);
            if ($configStatus === "IGNORED") {
                continue;
            }
            if ($configStatus !== "DONE") {

                $processInfo = [
                    "name" => $process->getName(),
                    "label" => $process->label,
                    "status" => "RUNNING"
                ];
                JobLog::setProcess($process->phase->module->name, $process->phase->name, $processInfo, $index);

                $exec = $process->execute();

                if ($exec['ret'] === false) {
                    $processInfo["error"] = $exec['output'];
                    $processInfo["status"] = "FAILED";
                } else {

                    $processInfo["status"] = "DONE";
                }

                JobLog::setProcess($process->phase->module->name, $process->phase->name, $processInfo, $index);
                if ($processInfo["status"] === "FAILED") {
                    return false;
                }
            } else {

                JobLog::displayOutput($process->phase->module->name, $process->phase->name, "process", "<warning>SKIP</warning>");
            }
        }
        return true;
    }

    public static function getParameterAnswer($moduleName, $paramName) {

        $data = ModuleJob::getJobData();

        $parameters=$data["parameters"];
        foreach ($parameters as $parameter) {
            if ($parameter["name"] === $paramName && $parameter["module"] === $moduleName) {
                return ($parameter["answer"]??"");
            }
        }
        return null;
    }
}