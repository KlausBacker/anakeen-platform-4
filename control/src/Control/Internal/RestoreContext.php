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

        $this->restoreDatacase();
        $this->updateContext();
        $this->reconfigureModules();

        JobLog::setStatus("restoring", "", ModuleJob::RUNNING_STATUS);
    }

    public function updateContext()
    {
        $wiff = \WIFF::getInstance();
        $context = Context::getContext();
        // Modify context's parameters
        $context->setParamByName('core_db', $this->pgService);
        $context->setParamByName('vault_root', $this->vaultsPath);

        $platformRoot = realpath(sprintf("%s/../platform", $wiff->root));
        $context->setAttribute('root', $platformRoot);
        $context->root = $platformRoot;


    }

    public function restoreDatacase()
    {

        JobLog::setStatus("restoring", self::PHASE_PGRESTORE, ModuleJob::RUNNING_STATUS);

        $dbFDump = $this->getDbDumpFile();
        $fileList = sprintf("/tmp/%s", uniqid("pgr"));

        $cmd = sprintf(
            "pg_restore %s -l | grep -v 'COMMENT - EXTENSION' > %s && pg_restore %s --use-list %s %s  | PGSERVICE=\"%s\" psql -q -v ON_ERROR_STOP=1  2>&1",
            escapeshellarg($dbFDump),
            escapeshellarg($fileList),
            $this->cleanDatabase ? "-c" : "",
            escapeshellarg($fileList),
            escapeshellarg($dbFDump),
            $this->pgService
        );

        System::exec($cmd);

        unlink($dbFDump);

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