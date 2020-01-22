<?php


namespace Control\Internal;

use Control\Exception\RuntimeException;

class RestoreContext
{
    const PHASE_PGRESTORE = "restore-database";
    const PHASE_RECONFIGURE = "reconfigure-parameters";

    protected $pgService;
    protected $vaultsPath;
    protected $cleanDatabase = true;

    public function restore()
    {
        JobLog::setStatus("restoring", "", ModuleJob::RUNNING_STATUS);

        $this->updateContext();
        $this->restoreDatacase();
        $this->reconfigureModules();

        JobLog::setStatus("restoring", "", ModuleJob::RUNNING_STATUS);
    }

    public function updateContext()
    {
        $wiff = \WIFF::getInstance();
        $context = Context::getContext(false);
        // Modify context's parameters
        $platformRoot = realpath(sprintf("%s/../platform", $wiff->root));
        $context->setAttribute('root', $platformRoot);
        $context->root = $platformRoot;

        $context->setParamByName('core_db', $this->pgService);
        $context->setParamByName('vault_root', $this->vaultsPath);
        $wiff->createHtaccessFile();
    }

    public function restoreDatacase()
    {
        JobLog::setStatus("restoring", self::PHASE_PGRESTORE, ModuleJob::RUNNING_STATUS);

        $dbFDump = $this->getDbDumpFile();
        $fileList = \Control\Internal\LibSystem::tempnam(null, "pgr");

        $cmds=[];

        if ($this->cleanDatabase) {
            $sqlClean = <<<SQL
DROP SCHEMA IF EXISTS public CASCADE;  
CREATE SCHEMA public;
DROP SCHEMA IF EXISTS family CASCADE;
SQL;
            $cleanDocCmd = sprintf(
                "PGSERVICE=\"%s\" psql -q -c %s",
                $this->pgService,
                escapeshellarg($sqlClean)
            );

            JobLog::addLog("restoring", self::PHASE_PGRESTORE, $cleanDocCmd);
            System::bashExec([$cleanDocCmd]);
        }

        $cmds[] = sprintf(
            "pg_restore %s -l | grep -v 'COMMENT - EXTENSION' > %s",
            escapeshellarg($dbFDump),
            escapeshellarg($fileList)
        );

        $cmds[] = sprintf(
            "pg_restore %s --no-owner -f- --use-list %s %s | PGSERVICE=\"%s\" psql -q -v ON_ERROR_STOP=1",
            $this->cleanDatabase ? "-c --if-exists" : "",
            escapeshellarg($fileList),
            escapeshellarg($dbFDump),
            $this->pgService
        );
        JobLog::addLog("restoring", self::PHASE_PGRESTORE, implode("\n", $cmds));
        System::bashExec($cmds);

        JobLog::setStatus("restoring", self::PHASE_PGRESTORE, ModuleJob::DONE_STATUS);
    }


    public function reconfigureModules()
    {
        JobLog::setStatus("restoring", self::PHASE_RECONFIGURE, ModuleJob::RUNNING_STATUS);

        $context = Context::getContext();

        $installedModuleList = $context->getInstalledModuleList();
        foreach ($installedModuleList as $module) {
            /**
             * @var \Module $module
             */
            $phase = $module->getPhase('reconfigure');
            $processList = $phase->getProcessList();
            if ($processList) {
                JobLog::setStatus($module->name, self::PHASE_RECONFIGURE, ModuleJob::RUNNING_STATUS);
                foreach ($processList as $index => $process) {
                    /**
                     * @var \Process $process
                     */
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
                }
            }
            JobLog::setStatus($module->name, self::PHASE_RECONFIGURE, ModuleJob::DONE_STATUS);
        }
        JobLog::setStatus("restoring", self::PHASE_RECONFIGURE, ModuleJob::DONE_STATUS);
    }

    /**
     * @param string $pgService
     */
    public function setPgService($pgService): void
    {
        $this->pgService = $pgService;
    }

    /**
     * @param string $vaultsPath
     */
    public function setVaultsPath($vaultsPath): void
    {
        $this->vaultsPath = $vaultsPath;
    }

    protected function getDbDumpFile()
    {
        $wiff = \WIFF::getInstance();
        $dbFile = realpath(sprintf("%s/../%s", $wiff->root, ArchiveContext::dbDumpFile));

        if (!file_exists($dbFile)) {
            throw new RuntimeException(sprintf("Db dump file \"%s\" not found", $dbFile));
        }

        return $dbFile;
    }

    /**
     * @param bool $cleanDatabase
     */
    public function setCleanDatabase(bool $cleanDatabase): void
    {
        $this->cleanDatabase = $cleanDatabase;
    }
}
