<?php

namespace Control\Internal;


use Control\Exception\RuntimeException;

class ArchiveContext
{
    const PHASE_PLATFORM = "archive-platform-files";
    const PHASE_CONTROL = "archive-control-files";
    const PHASE_DATABASE = "dump-database";
    const PHASE_VAULTS = "archive-vaults";

    const dbDumpFile = "core_db.pg_dump";
    /**
     * @var \Context
     */
    protected $context;
    /**
     * @var string
     */
    protected $errorMessage;
    /**
     * @var string
     */
    protected $warningMessage;

    protected $outputFile;
    protected $withVault = false;
    /**
     * @var ZipArchiveCmd
     */
    protected $zip;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * Create or write in error file for archive error
     *
     * @param string $phase
     */
    private function exitArchiveError($phase = "")
    {
        JobLog::setError("archiving", $phase, $this->errorMessage);
        JobLog::writeInterruption(ModuleJob::FAILED_STATUS);
        throw new RuntimeException($this->errorMessage);
    }

    /**
     * Archive context
     *
     *
     * @return bool|string false or archiveId
     */
    public function archiveContext()
    {
        $unlink = array();
        JobLog::setStatus("archiving", "", ModuleJob::RUNNING_STATUS);
        $archiveId = $this->__archiveContext($unlink);
        foreach ($unlink as $file => $meta) {
            if (!file_exists($file)) {
                continue;
            }
            unlink($file);
        }
        return $archiveId;
    }

    private function __archiveContext(&$unlink, $archiveName = "archive")
    {
        $wiff = \WIFF::getInstance();
        $wiff_root = $wiff->getWiffRoot();
        if ($wiff_root === false) {
            $this->errorMessage = sprintf("Could not get wiff root directory.");
            return false;
        }

        $archived_tmp_dir = $wiff->archived_tmp_dir;
        // --- Create or reuse directory --- //
        if (is_dir($archived_tmp_dir)) {
            if (!is_writable($archived_tmp_dir)) {
                $this->errorMessage = sprintf("Directory '%s' is not writable.", $archived_tmp_dir);
                return false;
            }
        } else {
            if (@mkdir($archived_tmp_dir) === false) {
                $this->errorMessage = sprintf("Error creating directory '%s'.", $archived_tmp_dir);
                return false;
            }
        }

        $this->zip = new ZipArchiveCmd();

        $archived_contexts_dir = $wiff->archived_contexts_dir;
        // --- Generate archive id --- //
        $datetime = new \DateTime();
        $archiveId = sprintf("%s-%s", preg_replace('/\//', '_', $archiveName), sha1($this->context->name . $datetime->format('Y-m-d H:i:s')));
        // --- Create status file for archive --- //
        $status_file = $archived_tmp_dir . DIRECTORY_SEPARATOR . $archiveId . '.sts';
        file_put_contents($status_file, $archiveName);
        $unlink[$status_file] = true;

        if (!is_dir($archived_contexts_dir)) {
            mkdir($archived_contexts_dir);
        }

        $zipfile = $this->outputFile;
        if ($this->zip->open($zipfile, ZipArchiveCmd::CREATE) === false) {
            $this->errorMessage = sprintf("Cannot create Zip archive '%s': %s", $zipfile, $this->zip->getStatusString());
            // --- Delete status file --- //
            $this->exitArchiveError();
            return false;
        }


        // Identify and exclude vaults located below the context directory


        $this->zipPlatformFiles();
        $this->zipControlFiles();
        $this->dumpDataBase();

        if ($this->withVault) {
            $this->zipVaults();
        }


        $this->zip->close();

        return $archiveId;
    }

    /**
     * Get vault list
     *
     * @return array|bool
     */
    private function getVaultList()
    {
        $pgservice_core = $this->context->getParamByName('core_db');
        $dbconnect = pg_connect("service=$pgservice_core");
        if ($dbconnect === false) {
            $this->errorMessage = sprintf("Error when trying to connect to database with service '%s'.", $pgservice_core);
            return false;
        }
        $result = pg_query($dbconnect, "SELECT id_fs, r_path FROM vaultdiskfsstorage ;");
        if ($result === false) {
            $this->errorMessage = sprintf("Error executing query: %s", pg_last_error($dbconnect));
            return false;
        }
        $vaultList = pg_fetch_all($result);
        if ($vaultList === false) {
            /* pg_fetch_all() returns false if the result is empty
             but we want an empty array() in this case. */
            $vaultList = array();
        }
        pg_close($dbconnect);
        return $vaultList;
    }


    protected function dumpDataBase()
    {

        // --- Generate database dump --- //
        JobLog::setStatus("archiving", self::PHASE_DATABASE, ModuleJob::RUNNING_STATUS);
        $wiff = \WIFF::getInstance();
        $archived_tmp_dir = $wiff->archived_tmp_dir;
        $pgservice_core = $this->context->getParamByName('core_db');

        $dump = $archived_tmp_dir . DIRECTORY_SEPARATOR . self::dbDumpFile;

        $errorFile = LibSystem::tempnam(null, 'WIFF_error.tmp');
        if ($errorFile === false) {
            $this->errorMessage = "Error creating temporary file for error.";
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_DATABASE);
            return false;
        }

        $script = sprintf("PGSERVICE=%s pg_dump -Fc --no-owner --if-exists --clean --compress=5 1>%s 2>%s", escapeshellarg($pgservice_core), escapeshellarg($dump),
            escapeshellarg($errorFile));
        try {
            System::exec($script);
        } catch (RuntimeException $e) {
            $this->errorMessage = "Error when making database dump :: " . file_get_contents($errorFile);
            if (file_exists("$errorFile")) {
                unlink("$errorFile");
            }
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_DATABASE);
        }
        $unlink[$dump] = true;


        $err = $this->zip->addFileWithoutPath($dump);
        if ($err === false) {
            $this->errorMessage = sprintf("Could not add 'core_db.pg_dump.gz' to archive: %s", $this->zip->getStatusString());
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_DATABASE);
            return false;
        }
        unlink($dump);
        unset($unlink[$dump]);
        JobLog::setStatus("archiving", self::PHASE_DATABASE, ModuleJob::DONE_STATUS);
        return true;
    }

    protected function zipPlatformFiles()
    {
        JobLog::setStatus("archiving", self::PHASE_PLATFORM, ModuleJob::RUNNING_STATUS);


        $wiff = \WIFF::getInstance();
        $realContextRootPath = realpath($this->context->root);
        if ($realContextRootPath === false) {
            $this->errorMessage = sprintf("Error getting real path for '%s'", $this->context->root);
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_PLATFORM);
            return false;
        }

        $tarExcludeOpts = '';
        $tarExcludeList = array(
            sprintf("-x %s/**\\*", ('./' . basename($realContextRootPath) . '/var/tmp')),
            sprintf("-x %s/**\\*", ('./' . basename($realContextRootPath) . '/var/session')),
            sprintf("-x %s/**\\*", ('./' . basename($realContextRootPath) . '/var/cache'))
        );
        $vaultList = $this->getVaultList();
        if ($vaultList === false) {
            $this->errorMessage = sprintf("Error getting vault list for context '%s'", $this->context->root);
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_PLATFORM);
            return false;
        }

        foreach ($vaultList as $vault) {
            $r_path = $vault['r_path'];
            if ($r_path[0] != '/') {
                $r_path = $this->context->root . DIRECTORY_SEPARATOR . $r_path;
            }
            $real_r_path = realpath($r_path);
            if ($real_r_path === false) {
                continue;
            }
            if (strpos($real_r_path, $realContextRootPath) === 0) {
                $relative_r_path = "./" . basename($realContextRootPath) . substr($real_r_path, strlen($realContextRootPath));
                $tarExcludeList[] = sprintf("-x %s/**\\* -x %s/", $relative_r_path, $relative_r_path);
            }
        }
        if (count($tarExcludeList) > 0) {
            $tarExcludeOpts = join(' ', $tarExcludeList);
        }

        if (file_exists($this->outputFile)) {
            if (!unlink($this->outputFile)) {
                throw new RuntimeException(sprintf("Cannot delete output file \"%s\"", $this->outputFile));
            }
        }


        $script = sprintf("cd %s;zip --symlink -r -q %s %s %s", escapeshellarg(dirname($realContextRootPath)), escapeshellarg($this->outputFile),
            escapeshellarg(basename($realContextRootPath)),
            $tarExcludeOpts);
        try {
            System::exec($script);
        } catch (RuntimeException $e) {
            $this->errorMessage = "Error when making context tar :: " . $e->getMessage();

            $this->zip->close();
            $this->exitArchiveError(self::PHASE_PLATFORM);
        }

        if ($wiff->verifyGzipIntegrity($this->outputFile, $err) === false) {
            $this->errorMessage = sprintf("Corrupted zip archive '%s': %s", $this->outputFile, $err);
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_PLATFORM);
            return false;
        }


        JobLog::setStatus("archiving", self::PHASE_PLATFORM, ModuleJob::DONE_STATUS);
        return true;
    }

    protected function zipControlFiles()
    {
        JobLog::setStatus("archiving", self::PHASE_CONTROL, ModuleJob::RUNNING_STATUS);


        $wiff = \WIFF::getInstance();
        $realControlRootPath = realpath($wiff->root);
        if ($realControlRootPath === false) {
            $this->errorMessage = sprintf("Error getting real path for '%s'", $wiff->root);
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_CONTROL);
            return false;
        }

        $tarExcludeOpts = '';
        $tarExcludeList = array(
            sprintf("-x %s**\\*", './' . basename($realControlRootPath) . '/' . \WIFF::archived_tmp_dir),
            sprintf("-x %s**\\*", './' . basename($realControlRootPath) . '/'. \WIFF::run_dir),
        );

        if (count($tarExcludeList) > 0) {
            $tarExcludeOpts = join(' ', $tarExcludeList);
        }
        //error_log(__METHOD__ . " " . sprintf("tarExcludeOpts = [%s]", $tarExcludeOpts));
        // --- Generate context tar.gz --- //
        $script = sprintf("cd %s;zip -ru -q %s %s %s", escapeshellarg(dirname($realControlRootPath)), escapeshellarg($this->outputFile),
            escapeshellarg(basename($realControlRootPath)),
            $tarExcludeOpts);

        try {
            System::exec($script);
        } catch (RuntimeException $e) {
            $this->errorMessage = "Error when update zip :: " . $e->getMessage();

            $this->zip->close();
            $this->exitArchiveError(self::PHASE_CONTROL);
        }

        if ($wiff->verifyGzipIntegrity($this->outputFile, $err) === false) {
            $this->errorMessage = sprintf("Corrupted gzip archive '%s': %s", $this->outputFile, $err);
            $this->zip->close();
            $this->exitArchiveError(self::PHASE_CONTROL);
            return false;
        }


        JobLog::setStatus("archiving", self::PHASE_CONTROL, ModuleJob::DONE_STATUS);
        return true;
    }

    protected function zipVaults()
    {

        JobLog::setStatus("archiving", self::PHASE_VAULTS, ModuleJob::RUNNING_STATUS);

        $wiff = \WIFF::getInstance();
        // --- Generate vaults tar.gz files --- //
        $vaultList = $this->getVaultList();
        if ($vaultList === false) {
            $this->errorMessage = sprintf("Error getting vault list: %s", $this->errorMessage);
            $this->zip->close();
            $this->exitArchiveError();
            return false;
        }

        $vaultTmpDir = sprintf("%s/vaults", $wiff->archived_tmp_dir);
        if (!is_dir($vaultTmpDir)) {

            if (!mkdir($vaultTmpDir)) {

                $this->errorMessage = sprintf("Error create vault dir '%s'", $vaultTmpDir);
                $this->exitArchiveError(self::PHASE_VAULTS);
                return false;
            }
        }
        $vaultDirList = array();
        foreach ($vaultList as $vault) {
            $id_fs = $vault['id_fs'];
            $r_path = $vault['r_path'];
            if (is_dir($r_path)) {
                $vaultDirList[] = array(
                    "id_fs" => $id_fs,
                    "r_path" => $r_path
                );
                $script = sprintf("cd %s;rm -f %s && ln -s %s %s", escapeshellarg($vaultTmpDir), escapeshellarg($id_fs), escapeshellarg($r_path), escapeshellarg($id_fs));
                try {
                    System::exec($script);
                } catch (RuntimeException $e) {
                    $this->errorMessage = sprintf("Error when linking vault '%s': %s", $r_path, $e->getMessage());
                    $this->zip->close();
                    $this->exitArchiveError(self::PHASE_VAULTS);
                }


                $script = sprintf("cd %s;zip -ru -q %s vaults/%s", escapeshellarg(dirname($vaultTmpDir)), escapeshellarg($this->outputFile), escapeshellarg($id_fs));

                System::exec($script);

            }
        }


        JobLog::setStatus("archiving", self::PHASE_VAULTS, ModuleJob::DONE_STATUS);
        return true;
    }

    /**
     * @param mixed $outputFile
     */
    public function setOutputFile($outputFile): void
    {
        $this->outputFile = $outputFile;
    }

    /**
     * @param bool $withVault
     */
    public function setWithVault(bool $withVault): void
    {
        $this->withVault = $withVault;
    }


}
