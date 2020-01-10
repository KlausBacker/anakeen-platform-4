<?php


namespace Control\Internal;

use Control\Cli\AskParameters;
use Control\Cli\CliStatus;
use Control\Exception\RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . "/../../../include/lib/Lib.Cli.php";

class ModuleJob
{
    const READY_STATUS = "Ready";
    const NOTINITIALIZED_STATUS = "Not Initialized";
    const INITIALIZED_STATUS = "Initialized";
    const RUNNING_STATUS = "Running";
    const DONE_STATUS = "Done";
    const MAINTENANCE_STATUS = "Maintenance";
    const TODO_STATUS = "Todo";
    const INTERRUPTED_STATUS = "Interrupted";
    const ERROR_STATUS = "Error";
    const FAILED_STATUS = "Failed";

    protected static $jobData = null;
    protected static $processIndex = 0;

    protected static function getRunDir()
    {
        $wiff = \WIFF::getInstance();
        $rundir = $wiff->run_dir;
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

    public static function getJobStatus()
    {
        $status = ["status" => "Activated"];

        $isInitialized = Context::isInitialized();
        if (!$isInitialized) {
            $status["status"] = ModuleJob::NOTINITIALIZED_STATUS;
            $status["message"] = "Context not initialized";
        } else {
            $context = Context::getContext();
            if (self::isRunning()) {
                $status = self::getJobData();
                $status["status"] = ModuleJob::RUNNING_STATUS;
            } elseif (self::hasFailed()) {
                $jobData = self::getJobData();
                $status = $jobData;
            } elseif (!$context->getInstalledModuleList()) {
                $status["status"] = ModuleJob::INITIALIZED_STATUS;
                $status["message"] = "Ready to install modules";
            } else {
                $platformStatus = Platform::getStatusInfo();
                $status["status"] = ModuleJob::READY_STATUS;
                if ($platformStatus) {
                    if ($platformStatus["maintenance"] ?? null) {
                        $status["status"] = ModuleJob::MAINTENANCE_STATUS;
                    }
                    if ($platformStatus["error"] ?? null) {
                        $status["error"] = $platformStatus["error"] ?? "";
                    } else {
                        $status["message"] = $platformStatus["status"];
                    }
                }
            }
        }

        return $status;
    }

    public static function getJobData()
    {
        $file = self::getJobFile();
        if (file_exists($file)) {
            $fp = fopen($file, "r");
            if (flock($fp, LOCK_EX)) {
                $content = "";
                while (!feof($fp)) {
                    $content .= fread($fp, 8192);
                }
                flock($fp, LOCK_UN);
            } else {
                throw new RuntimeException(sprintf("Cannot lock job file \"%s\"", $file));
            }
            fclose($fp);
            if (!$content) {
                return null;
            }
        } else {
            return null;
        }

        return json_decode($content, true);
    }


    protected static function putJobData($data)
    {
        $file = self::getJobFile();

        $fp = fopen($file, "w+");
        if ($fp === false) {
            return false;
        }

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
            fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            throw new RuntimeException(sprintf("Cannot lock job file \"%s\"", $file));
        }

        fclose($fp);

        return true;
    }

    protected static function archiveJobFile($copy = false)
    {
        $file = self::getJobFile();
        Log::copyFile($file, $copy);
    }

    protected static function getPidFile()
    {
        return sprintf("%s/pid", self::getRunDir());
    }

    public static function waitRunning(ConsoleOutput $output)
    {
        $verbose = $output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE;
        $veryVerbose = $output->getVerbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE;
        if ($verbose) {
            CliStatus::formatJobStatusOutput($output);
            /** @var ConsoleSectionOutput $section */
            $section = $output->section();
        } else {
            $section = null;
        }
        declare(ticks=1);
        $warningQuit = function () use ($output) {
            $output->writeln("\r<info>Display status interrupted.</info>");
            $output->writeln("<warning>Job continue running in background task</warning>");
            exit;
        };
        // Catch Ctrl-C signal
        pcntl_signal(SIGINT, $warningQuit);

        $waitInterval = 500000;
        do {
            // In case of previous fail
            usleep($waitInterval);
            $jobStatus = self::getJobStatus();
            $status = $jobStatus["status"] ?? "";
            if ($veryVerbose) {
                print $status . " - ";
            } else {
                print ".";
            }
        } while ($status === self::MAINTENANCE_STATUS);

        do {
            usleep($waitInterval);
            $jobStatus = self::getJobStatus();
            $status = $jobStatus["status"] ?? "";
            if ($status === self::READY_STATUS && !isset($jobStatus["tasks"])) {
                // The job is already finished
                break;
            }
            if ($veryVerbose) {
                print $status . " - ";
            } else {
                print ".";
            }
        } while ($status === self::READY_STATUS);

        if ($verbose || $veryVerbose) {
            print "\r";
        }

        do {
            $jobStatus = self::getJobStatus();
            $status = $jobStatus["status"] ?? "";
            usleep($waitInterval);
            if ($veryVerbose === true) {
                self::displayLog($jobStatus);
            } elseif ($verbose && $section) {
                $section->clear();
                CliStatus::displayJobStatus($section, $jobStatus);
            } else {
                print ".";
            }
        } while ($status === self::RUNNING_STATUS);

        print "\n";
        if ($status !== self::READY_STATUS) {
            throw new RuntimeException("$status :" . JobLog::getLastError());
        }
    }

    protected static function displayLog(array $jobStatus)
    {
        static $index = 0;

        $logs = $jobStatus["log"] ?? [];


        $newlogs = array_slice($logs, $index);
        $index = count($logs);

        foreach ($newlogs as $log) {
            if (is_array($log["value"])) {
                $log["value"] = implode(", ", $log["value"]);
            }
            $row = [
                sprintf("[%s]  %s", $log["date"], $log["module"]),
                sprintf("%s", $log["phase"]),
                sprintf("%s", $log["task"]),
                sprintf("%s", $log["value"])
            ];

            if ($log["task"] === "error") {
                array_unshift($row, "ERROR");
            }
            print implode(" - ", $row) . "\n";
        }
    }

    public static function recordJobTask($data)
    {
        $jobFile = self::getJobFile();
        if (!self::putJobData($data)) {
            throw new RuntimeException(sprintf("Cannot write job file \"%s\"", $jobFile));
        }
    }

    public static function initJobTask(ModuleManager $module, $options = [])
    {
        if (self::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }
        $data["action"] = $module->getMainPhase();
        $data["moduleArg"] = $module->getName();
        $data["file"] = $module->getFile();
        $data["moduleFileForceInstall"] = $module->isModuleFileForceInstall();
        $data["options"] = $options;
        $dependencies = $module->getDepencies();
        foreach ($dependencies as $dependency) {
            $task = [
                "module" => $dependency->name,
                "action" => $dependency->needphase,
                "status" => ModuleJob::TODO_STATUS
            ];
            if ($data["action"] !== "remove") {
                $task["phases"][] = ["name" => "download", "status" => ModuleJob::TODO_STATUS];
            }

            foreach ($dependency->getPhaseList($dependency->needphase) as $phase) {
                $task["phases"][] = ["name" => $phase, "status" => ModuleJob::TODO_STATUS];
            }

            if ($data["action"] === "remove") {
                $task["phases"][] = ["name" => "uninstall", "status" => ModuleJob::TODO_STATUS];
            }

            $data["tasks"][] = $task;
        }

        $data["parameters"] = $module->getAllParameters();
        self::recordJobTask($data);
    }


    public static function isReady()
    {
        if (!Context::isInitialized()) {
            return false;
        }
        $context = Context::getContext();
        if (!$context->getInstalledModuleList()) {
            return false;
        }
        return true;
    }

    public static function isRunning()
    {
        $pidFile = self::getPidFile();
        if (!file_exists($pidFile)) {
            return false;
        }
        $pid = intval(file_get_contents($pidFile));
        if (!posix_kill($pid, 0)) {
            JobLog::writeInterruption();
            $err = posix_strerror(posix_get_last_error());
            throw new RuntimeException(
                sprintf(
                    "[$err] Seems job process is died. \n Try to remove \"./control/%spid\" file.",
                    \WIFF::run_dir
                )
            );
        }
        return true;
    }

    public static function hasFailed(): bool
    {
        $jobFile = self::getJobFile();
        if (file_exists($jobFile)) {
            $data = self::getJobData();

            $status = $data["status"] ?? "";
            if ($status === self::ERROR_STATUS || $status === self::INTERRUPTED_STATUS || $status === self::FAILED_STATUS) {
                return true;
            }
        }


        return false;
    }

    public static function killJob()
    {
        if (!ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("No job detected."));
        }

        $pidFile = self::getPidFile();
        $pid = intval(file_get_contents($pidFile));
        if ($pid) {
            if (!posix_kill(-($pid), SIGTERM)) {
                throw new RuntimeException(sprintf(
                    "Fail to kill process.May be process \"%s\" not exists.\nIf it process not exists, you could delete \"./control/%spid\" file",
                    \WIFF::run_dir,
                    $pid
                ));
            }
            return $pid;
        }
        return false;
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

            if (self::hasFailed()) {
                ModuleJob::archiveJobFile(true);
            }

            JobLog::clearLog();

            self::$jobData = self::getJobData();
            if (!self::$jobData) {
                throw new RuntimeException(sprintf("Unreadable job file \"%s\"", $jobFile));
            }
            self::dotheJob();
        } catch (\Exception $e) {
            if ($pidFile && file_exists($pidFile)) {
                unlink($pidFile);
            }

            JobLog::setStatus("", "", "Error");
            JobLog::setError("", "", $e->getMessage());


            throw $e;
        }
        unlink($pidFile);
    }

    protected static function catchExit()
    {
        set_error_handler(function (/** @noinspection PhpUnusedParameterInspection */ $error, $msg) {
            JobLog::setStatus("", "", "Error");
            JobLog::setError("", "", $msg);
            return false;
        });
        $pidFile = self::getPidFile();
        register_shutdown_function(function () use ($pidFile) {
            if ($pidFile && file_exists($pidFile)) {
                unlink($pidFile);
            }
            LibSystem::purgeTmpFiles();
        });
        declare(ticks=1);

        $signalHandler = function () use ($pidFile) {
            JobLog::writeInterruption();
            if ($pidFile && file_exists($pidFile)) {
                unlink($pidFile);
            }
            exit();
        };
        // Catch Ctrl-C signal
        pcntl_signal(SIGINT, $signalHandler);
        pcntl_signal(SIGQUIT, $signalHandler);
        // Catch Normal KILL
        pcntl_signal(SIGTERM, $signalHandler);
    }

    protected static function dotheJob()
    {
        JobLog::setStatus("", "", ModuleJob::RUNNING_STATUS);
        $moduleName = self::$jobData["moduleArg"] ?? "";
        $moduleFileName = self::$jobData["file"] ?? "";
        $action = self::$jobData["action"];
        $module = null;
        if ($action !== "restore") {
            if ($moduleFileName) {
                $module = new ModuleManager("");
                $reinstall = self::$jobData["moduleFileForceInstall"] ?? false;
                $module->setFile($moduleFileName, $reinstall);
            } elseif ($moduleName) {
                $module = new ModuleManager($moduleName);
            } else {
                $module = new ModuleManager("");
            }
        }
        $jobStatus = true;
        switch ($action) {
            case "install":
                $module->prepareInstall(true);
                $jobStatus = self::installDependencies($module);
                break;
            case "upgrade":
                $module->prepareUpgrade(true);
                $jobStatus = self::installDependencies($module);
                break;
            case "remove":
                $module->prepareRemove();
                $jobStatus = self::removeModule($module);
                break;
            case "archive":
                $archive = new ArchiveContext();
                $archive->setOutputFile(self::$jobData["output"]);
                $archive->setWithVault(self::$jobData["with-vault"]);
                $archive->archiveContext();
                break;
            case "restore":
                $restore = new RestoreContext();
                $restore->setPgService(self::$jobData["pg-service"]);
                $restore->setVaultsPath(self::$jobData["vaults-path"]);
                $restore->setCleanDatabase(self::$jobData["force-clean"]);

                $restore->restore();
                break;
        }

        if ($jobStatus) {
            JobLog::setStatus("", "", ModuleJob::DONE_STATUS);
            // Job succeeded
            self::archiveJobFile();
            AskParameters::removeAskes();
        } else {
            JobLog::setStatus("", "", ModuleJob::FAILED_STATUS);

            throw new RuntimeException(JobLog::getLastError());
        }
    }

    /**
     * @param ModuleManager $moduleManager
     *
     * @return bool
     * @throws \Exception
     */
    public static function installDependencies(ModuleManager $moduleManager)
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
            JobLog::setInfo($module->name, "status", ModuleJob::RUNNING_STATUS);

            if ($module->needphase === 'replaced') {
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
                    JobLog::setError(
                        $module->name,
                        $module->needphase,
                        sprintf(
                            "Error: could not unregister module '%s' from context: %s\n",
                            $module->name,
                            $context->errorMessage
                        )
                    );
                    return false;
                }
                $ret = $context->deleteFilesFromModule($module->name);
                if ($ret === false) {
                    JobLog::setError(
                        $module->name,
                        $module->needphase,
                        sprintf(
                            "Error: could not delete files for module '%s': %s\n",
                            $module->name,
                            $context->errorMessage
                        )
                    );
                    return false;
                }
                $ret = $context->deleteManifestForModule($module->name);
                if ($ret === false) {
                    JobLog::setError(
                        $module->name,
                        $module->needphase,
                        sprintf(
                            "Error: could not delete manifest file for module '%s': %s\n",
                            $module->name,
                            $context->errorMessage
                        )
                    );
                    return false;
                }

                continue;
            }

            if ($module->status == 'downloaded' && is_file($module->tmpfile)) {
                JobLog::setStatus($module->name, "download", ModuleJob::DONE_STATUS);
                JobLog::setInfo($module->name, "downloadFile", $module->tmpfile);
            } else {
                if (JobLog::getStatus($module->name, "download") !== ModuleJob::DONE_STATUS) {
                    JobLog::setStatus($module->name, "download", ModuleJob::RUNNING_STATUS);
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

                    JobLog::setStatus($module->name, "download", ModuleJob::DONE_STATUS);
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


                    $value = self::getParameterAnswer($module->name, $param->name);

                    if ($value === null && $param->default !== '') {
                        $value = $param->default;
                    }

                    if ($value === null && $param->needed) {
                        throw new RuntimeException(sprintf("Error: could not read answer for \"%s\"!", $param->name));
                    }
                    $param->value = $param::cleanXMLUTF8($value);

                    $ret = $module->storeParameter($param);
                    if ($ret === false) {
                        throw new RuntimeException(sprintf("Error: could not store parameter '%s'!\n", $param->name));
                    }
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
                        if (JobLog::getStatus($module->name, $phaseName) !== ModuleJob::DONE_STATUS) {
                            JobLog::setStatus($module->name, $phaseName, ModuleJob::RUNNING_STATUS);
                            $ret = $context->deleteFilesFromModule($module->name);
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf(
                                        "Error: could not delete old files for module '%s' in '%s': %s",
                                        $module->name,
                                        $context->root,
                                        $context->errorMessage
                                    )
                                );
                                return false;
                            }
                            $ret = $module->unpack($context->root);
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf(
                                        "Error: could not unpack module '%s' in '%s': %s",
                                        $module->name,
                                        $context->root,
                                        $module->errorMessage
                                    )
                                );
                                return false;
                            }
                            JobLog::setStatus($module->name, $phaseName, ModuleJob::DONE_STATUS);
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    case 'unpack':
                        if (JobLog::getStatus($module->name, $phaseName) !== ModuleJob::DONE_STATUS) {
                            JobLog::setStatus($module->name, $phaseName, ModuleJob::RUNNING_STATUS);
                            $ret = $module->unpack($context->root);
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf(
                                        "Error: could not unpack module '%s' in '%s': %s",
                                        $module->name,
                                        $context->root,
                                        $module->errorMessage
                                    )
                                );
                                return false;
                            }
                            JobLog::setStatus($module->name, "unpack", ModuleJob::DONE_STATUS);
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    case 'unregister-module':
                        if (JobLog::getStatus($module->name, $phaseName) !== ModuleJob::DONE_STATUS) {
                            JobLog::setStatus($module->name, $phaseName, ModuleJob::RUNNING_STATUS);
                            $ret = $context->removeModule($module->name);
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf(
                                        "Error: could not remove module '%s' in '%s': %s\n",
                                        $module->name,
                                        $context->root,
                                        $context->errorMessage
                                    )
                                );
                                return false;
                            }
                            $ret = $context->deleteFilesFromModule($module->name);
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf(
                                        "Error: could not delete files for module '%s' in '%s': %s\n",
                                        $module->name,
                                        $context->root,
                                        $context->errorMessage
                                    )
                                );
                                return false;
                            }
                            $ret = $context->deleteManifestForModule($module->name);
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf("Error: could not delete manifest")
                                );
                                return false;
                            }

                            JobLog::setStatus($module->name, $phaseName, ModuleJob::DONE_STATUS);
                        } else {
                            JobLog::displayOutput($module->name, $phaseName, "status", "<warning>SKIP</warning>");
                        }
                        break;

                    case 'purge-unreferenced-parameters-value':
                        if (JobLog::getStatus($module->name, $phaseName) !== ModuleJob::DONE_STATUS) {
                            JobLog::setStatus($module->name, $phaseName, ModuleJob::RUNNING_STATUS);
                            $ret = $context->purgeUnreferencedParametersValue();
                            if ($ret === false) {
                                JobLog::setError(
                                    $module->name,
                                    $phaseName,
                                    sprintf(
                                        "Error: could not purge unreferenced parameters value in '%s': %s\n",
                                        $context->root,
                                        $context->errorMessage
                                    )
                                );
                                return false;
                            }
                            JobLog::setStatus($module->name, $phaseName, ModuleJob::DONE_STATUS);
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
                    JobLog::setError(
                        $module->name,
                        "",
                        sprintf(
                            "Error: Could not remove old installed module '%s': %s\n",
                            $module->name,
                            $context->errorMessage
                        )
                    );

                    return false;
                }
            }
            $ret = $module->setStatus('installed');
            if ($ret === false) {
                JobLog::setError(
                    $module->name,
                    "",
                    sprintf(
                        "Error: Could not set installed status on module '%s': %s\n",
                        $module->name,
                        $module->errorMessage
                    )
                );
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

    public static function removeModule(ModuleManager $moduleManager)
    {
        $context = Context::getContext();
        $module = $moduleManager->getInstalledModule($moduleManager->getName());
        if ($module === false) {
            throw new RuntimeException(sprintf("Could not find a module with name '%s'.", $moduleManager->getName()));
        }


        if (($ret = self::executeModulePhase($module, "pre-delete")) != 0) {
            throw new RuntimeException(sprintf("PRE-DELETE Phase error '%s'.", $moduleManager->getName()));
        }

        JobLog::setStatus($module->name, "uninstall", ModuleJob::RUNNING_STATUS);

        $index = 0;

        // -------- Remove reference to module ------------
        $label = "Remove reference to module";
        JobLog::setProcess(
            $module->name,
            "uninstall",
            ["label" => $label, "status" => ModuleJob::RUNNING_STATUS],
            $index
        );
        $ret = $context->removeModule($module->name);
        if ($ret === false) {
            JobLog::setStatus($module->name, "uninstall", ModuleJob::FAILED_STATUS);
            JobLog::setProcess(
                $module->name,
                "uninstall",
                ["label" => $label, "status" => ModuleJob::FAILED_STATUS],
                $index
            );
            throw new RuntimeException(sprintf(
                "Error removing module  '%s' : %s.",
                $moduleManager->getName(),
                $context->errorMessage
            ));
        }
        JobLog::setProcess($module->name, "uninstall", ["label" => $label, "status" => ModuleJob::DONE_STATUS], $index);


        // -------- Remove files of module ------------
        $index++;
        $label = "Remove files of module";
        JobLog::setProcess(
            $module->name,
            "uninstall",
            ["label" => $label, "status" => ModuleJob::RUNNING_STATUS],
            $index
        );

        $ret = $context->deleteFilesFromModule($module->name);
        if ($ret === false) {
            JobLog::setStatus($module->name, "uninstall", ModuleJob::FAILED_STATUS);
            JobLog::setProcess(
                $module->name,
                "uninstall",
                ["label" => $label, "status" => ModuleJob::FAILED_STATUS],
                $index
            );
            throw new RuntimeException(sprintf(
                "Error removing files from module  '%s' : %s.",
                $moduleManager->getName(),
                $context->errorMessage
            ));
        }
        JobLog::setProcess($module->name, "uninstall", ["label" => $label, "status" => ModuleJob::DONE_STATUS], $index);


        // -------- Remove manifest of module ------------
        $index++;
        $label = "Remove manifest of module";
        JobLog::setProcess(
            $module->name,
            "uninstall",
            ["label" => $label, "status" => ModuleJob::RUNNING_STATUS],
            $index
        );

        $ret = $context->deleteManifestForModule($module->name);
        if ($ret === false) {
            JobLog::setStatus($module->name, "uninstall", ModuleJob::FAILED_STATUS);
            JobLog::setProcess(
                $module->name,
                "uninstall",
                ["label" => $label, "status" => ModuleJob::FAILED_STATUS],
                $index
            );
            throw new RuntimeException(sprintf(
                "Error removing manifest from module  '%s' : %s.",
                $moduleManager->getName(),
                $context->errorMessage
            ));
        }
        JobLog::setProcess($module->name, "uninstall", ["label" => $label, "status" => ModuleJob::DONE_STATUS], $index);

        JobLog::setStatus($module->name, "uninstall", ModuleJob::DONE_STATUS);

        return true;
    }

    protected static function executeModulePhase(\Module $module, $phaseName)
    {
        $phase = $module->getPhase($phaseName);
        $processList = $phase->getProcessList();
        $ret = true;
        if (JobLog::getStatus($module->name, $phase->name) !== ModuleJob::DONE_STATUS) {
            JobLog::setStatus($module->name, $phase->name, ModuleJob::RUNNING_STATUS);
            $ret = self::executeProcessList($processList);
            if ($ret !== true) {
                JobLog::writeInterruption(ModuleJob::FAILED_STATUS);
            } else {
                JobLog::setStatus($module->name, $phase->name, ModuleJob::DONE_STATUS);
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
            if ($configStatus !== ModuleJob::DONE_STATUS) {
                $processInfo = [
                    "name" => $process->getName(),
                    "label" => $process->label,
                    "status" => ModuleJob::RUNNING_STATUS
                ];
                JobLog::setProcess($process->phase->module->name, $process->phase->name, $processInfo, $index);

                $exec = $process->execute();

                if ($exec['ret'] === false) {
                    $processInfo["error"] = $exec['output'];
                    $processInfo["status"] = ModuleJob::FAILED_STATUS;
                } else {
                    $processInfo["status"] = ModuleJob::DONE_STATUS;
                }

                JobLog::setProcess($process->phase->module->name, $process->phase->name, $processInfo, $index);
                if ($processInfo["status"] === ModuleJob::FAILED_STATUS) {
                    return false;
                }
            } else {
                JobLog::displayOutput(
                    $process->phase->module->name,
                    $process->phase->name,
                    "process",
                    "<warning>SKIP</warning>"
                );
            }
        }
        return true;
    }

    public static function getParameterAnswer($moduleName, $paramName)
    {
        $data = ModuleJob::getJobData();

        $parameters = $data["parameters"];
        foreach ($parameters as $parameter) {
            if ($parameter["name"] === $paramName && $parameter["module"] === $moduleName) {
                return (isset($parameter["answer"]) ? $parameter["answer"] : null);
            }
        }
        return null;
    }
}
