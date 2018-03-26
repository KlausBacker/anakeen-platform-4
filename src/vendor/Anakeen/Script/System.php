<?php

namespace Anakeen\Script;

use Anakeen\Core\ContextManager;
use Dcp\Core\DbManager;

class System
{
    /**
     * @var IStdio
     */
    protected $stdio = null;
    protected $verbose = false;
    protected $contextRoot = false;

    public function __construct()
    {
        $this->setVerbose(false);
        $this->setStdio(new Stdio());
        $this->setContextRoot(DEFAULT_PUBDIR);
    }


    public function start()
    {
        $this->reapplyDatabaseParameters();
        $this->clearAutoloadCache();
        $this->imageAndDocsLinks();
        $this->clearFileCache();
        $this->refreshJsVersion();
        $this->resetRouteConfig();
        $this->style();
        $this->unStop();
    }


    protected function setContextRoot($contextRoot)
    {
        if (!is_string($contextRoot) || strlen($contextRoot) <= 0) {
            throw new Exception(sprintf("contextRoot must not be empty."));
        }
        if (!is_dir($contextRoot)) {
            throw new Exception(sprintf("contextRoot '%s' is not a directory.", $contextRoot));
        }
        if (!is_readable($contextRoot)) {
            throw new Exception(sprintf("contextRoot '%s' is not readable.", $contextRoot));
        }
        if (!is_writable($contextRoot)) {
            throw new Exception(sprintf("contextRoot '%s' is not writable.", $contextRoot));
        }
        if (($realContextRoot = realpath($contextRoot)) === false) {
            throw new Exception(sprintf("could not get real path from contextRoot '%s'.", $contextRoot));
        }
        $this->contextRoot = $realContextRoot;
    }

    /**
     * Scan given directory and delete dead symlinks (i.e. symlinks pointing to non-existing files)
     *
     * @param string $dir
     *
     * @throws Exception
     */
    protected function deleteDeadLinks($dir)
    {
        if (($dh = opendir($dir)) === false) {
            throw new Exception(sprintf("Error opening directory '%s'.", $dir));
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $absLink = $this->absolutize($dir . DIRECTORY_SEPARATOR . $file);
            if (!is_link($absLink)) {
                continue;
            }
            $target = readlink($absLink);
            if ($target === false) {
                continue;
            }
            if (substr($target, 0, 1) != '/') {
                $target = dirname($absLink) . DIRECTORY_SEPARATOR . $target;
            }
            if (file_exists($target)) {
                continue;
            }
            $this->verbose(2, sprintf("Deleting link '%s' to non-existing file '%s'.\n", $absLink, $target));
            if (unlink($absLink) === false) {
                closedir($dh);
                throw new Exception(sprintf("Error deleting dead symlink '%s' to '%s'.", $absLink, $target));
            }
        }
        closedir($dh);
    }

    /**
     * Link files from source dir to destination dir.
     *
     * @param string $sourceDir Source dir from which files are to be linked
     * @param string $destDir   Destination dir to which the symlinks will be created
     * @param array  $linked    List of conflicting/duplicates files (i.e. source files with the same name)
     *
     * @throws Exception
     */
    public function linkFiles($sourceDir, $destDir, &$linked = array())
    {
        $this->verbose(2, sprintf("Processing files from '%s'.\n", $sourceDir));
        if (($dh = opendir($this->publize($sourceDir))) === false) {
            throw new Exception(sprintf("Error opening directory '%s'.", $this->publize($sourceDir)));
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $relSourceFile = $this->relativize($sourceDir . DIRECTORY_SEPARATOR . $file);
            $absSourceFile = $this->publize($relSourceFile);
            if (!is_file($absSourceFile) && !is_dir($absSourceFile)) {
                continue;
            }
            $relTarget = '..' . DIRECTORY_SEPARATOR . $relSourceFile;
            $absLink = $this->publize($destDir . DIRECTORY_SEPARATOR . basename($relSourceFile));
            if (!isset($linked[$absLink])) {
                $linked[$absLink] = array();
            }
            if (is_link($absLink)) {
                $source = readlink($absLink);
                if ($source !== false && $source == $relTarget) {
                    $linked[$absLink][] = $relTarget;
                    continue;
                }
                if (unlink($absLink) === false) {
                    closedir($dh);
                    throw new Exception(sprintf("Error removing symlink '%s'.", $absLink));
                }
            }
            $this->verbose(2, sprintf("Linking '%s' to '%s'.\n", $relTarget, $absLink));
            if (symlink($relTarget, $absLink) === false) {
                closedir($dh);
                throw new Exception(sprintf("Error symlinking '%s' to '%s'.", $relTarget, $absLink));
            }
            $linked[$absLink][] = $relTarget;
        }
        closedir($dh);
    }

    /**
     * Create a directory if it does not already exists...
     *
     * @param string $dir
     *
     * @throws Exception
     */
    protected function mkdir($dir)
    {
        if (is_dir($dir)) {
            return;
        }
        if (mkdir($dir) === false) {
            throw new Exception(sprintf("Error creating directory '%s'.", $dir));
        }
    }

    /**
     * Remove files matching the specified regex in the given directory
     *
     * @param $dir
     * @param $regex
     *
     * @throws Exception
     */
    protected function removeFilesByRegex($dir, $regex)
    {
        if (($dh = opendir($dir)) === false) {
            throw new Exception(sprintf("Error opening directory '%s'.", $dir));
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $ret = preg_match($regex, $file);
            if ($ret === false) {
                closedir($dh);
                throw new Exception(sprintf("Malformed regex pattern '%s'.", $regex));
            }
            if ($ret === 0) {
                continue;
            }
            $this->verbose(2, sprintf("Removing '%s'.\n", $dir . DIRECTORY_SEPARATOR . $file));
            if (unlink($dir . DIRECTORY_SEPARATOR . $file) == false) {
                closedir($dh);
                throw new Exception(sprintf("Error removing file '%s'.", $file));
            }
        }
        closedir($dh);
    }

    /**
     * Returns surdirs containing a specific subdir
     *
     * @param $subdir
     *
     * @return string[] list of dir/subdir relative to contextRoot
     */
    public function getSubDirs($subdir)
    {
        $appImagesDirs = array();
        if (($dh = opendir($this->contextRoot . "/public")) === false) {
            return $appImagesDirs;
        }
        while (($elmt = readdir($dh)) !== false) {
            if ($elmt == '.' || $elmt == '..') {
                continue;
            }

            if ($elmt === 'supervisor') {
                continue;
            }
            if (!is_dir($this->publize($elmt))) {
                continue;
            }
            if (!is_dir($this->publize($elmt . DIRECTORY_SEPARATOR . $subdir))) {
                continue;
            }
            $appImagesDirs[] = $elmt . DIRECTORY_SEPARATOR . $subdir;
        }
        closedir($dh);
        return $appImagesDirs;
    }

    public function getImagesDirs()
    {
        return $this->getSubDirs('Images');
    }

    public function getDocsDirs()
    {
        return $this->getSubDirs('Docs');
    }

    protected function debug($msg)
    {
        $this->stdio->wstart_stderr($msg);
    }

    /**
     * Print a message with the specified verbose level.
     *
     * @param $level
     * @param $msg
     */
    protected function verbose($level, $msg)
    {
        if ($this->verbose <= 0) {
            return;
        }
        if ($level <= $this->verbose) {
            $this->stdio->wstart_stdout($msg);
        }
    }

    /**
     * @param int $verbose Verbose level (e.g. 1, 2, etc.)
     *
     * @return bool
     */
    public function setVerbose($verbose)
    {
        $previous = $this->verbose;
        $this->verbose = (int)$verbose;
        return $previous;
    }

    /**
     * @param $stdio
     *
     * @return IStdio
     * @throws Exception
     */
    public function setStdio($stdio)
    {
        if (!is_a($stdio, '\Anakeen\Script\IStdio')) {
            throw new Exception(sprintf("Wrong class for stdioInterface: %s", get_class($stdio)));
        }
        $previous = $this->stdio;
        $this->stdio = $stdio;
        return $previous;
    }

    /**
     * Compute absolute path from context's root
     *
     * - If the file is relative, then the absolute path is computed relative to the context's root.
     * - If the file is already in a absolute form, then their current absolute form is used.
     *
     * @param $file
     *
     * @return string
     */
    public function absolutize($file)
    {
        if (substr($file, 0, 1) != '/') {
            $file = $this->contextRoot . DIRECTORY_SEPARATOR . $file;
        }
        return $file;
    }

    /**
     * Compute absolute path from context's root
     *
     * - If the file is relative, then the absolute path is computed relative to the context's root.
     * - If the file is already in a absolute form, then their current absolute form is used.
     *
     * @param $file
     *
     * @return string
     */
    protected function publize($file)
    {
        if (substr($file, 0, 1) != '/') {
            $file = $this->contextRoot . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $file;
        }
        return $file;
    }

    /**
     * Compute relative path from context's root
     *
     * - If the file is already in a relative form, then their current relative form is used.
     * - If the file is absolute and located under the context'root, then the relative path from the context's root is
     *   used.
     * - If the file is absolute and located outside the context's root, then an exception is thrown.
     *
     * @param $file
     *
     * @return string
     * @throws Exception
     */
    public function relativize($file)
    {
        if (substr($file, 0, 1) != '/') {
            return $file;
        }
        if ($file == $this->contextRoot) {
            return '.';
        }
        if (strpos($file, $this->contextRoot . DIRECTORY_SEPARATOR) === 0) {
            $file = substr($file, strlen($this->contextRoot . DIRECTORY_SEPARATOR));
            if ($file == '') {
                $file = '.';
            }
            return $file;
        }
        throw new Exception(sprintf("Could not relativize '%s' to '%s'.", $file, $this->contextRoot));
    }

    /**
     * @param $file
     * @param $callback
     *
     * @throws Exception
     */
    public function sedFile($file, $callback)
    {
        if (($perms = fileperms($file)) === false) {
            throw new Exception(sprintf("Error reading permissions for '%s'.", $file));
        }
        $content = file_get_contents($file);
        if ($content === false) {
            throw new Exception(sprintf("Error reading content from '%s'.", $file));
        }
        $content = call_user_func_array($callback, array(
            $content
        ));
        $tmpFile = tempnam(ContextManager::getTmpDir(), 'sedFile');
        if ($tmpFile === false) {
            throw new Exception(sprintf("Error creating temporary file."));
        }
        if (file_put_contents($tmpFile, $content) === false) {
            unlink($tmpFile);
            throw new Exception(sprintf("Error writing content to temporary file '%s'.", $tmpFile));
        }
        if (rename($tmpFile, $file) === false) {
            unlink($tmpFile);
            throw new Exception(sprintf("Error renaming '%s' to '%s'.", $tmpFile, $file));
        }
        /* Replicate original rights with extended rights */
        $perms = $perms & 07777;
        if (chmod($file, $perms) === false) {
            throw new Exception(sprintf("Error applying permissions '%o' to '%s'.", $perms, $file));
        }
    }


    /**
     *
     */
    public function clearAutoloadCache()
    {
        $this->verbose(1, sprintf("[+] Re-generating class autoloader.\n"));
        require_once sprintf('%s/vendor/Anakeen/WHAT/classAutoloader.php', $this->contextRoot);
        \Dcp\Autoloader::forceRegenerate();
        $this->verbose(1, sprintf("[+] Done.\n"));
    }


    public function resetRouteConfig()
    {
        $this->verbose(1, sprintf("[+] Update global route access definition.\n"));

        $routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
        $routeConfig->recordAccesses();

        $this->verbose(1, sprintf("[+] Reset cache route configuration file.\n"));
        $routesConfig = new \Anakeen\Router\RoutesConfig();
        $routesConfig->resetCache();

        $this->verbose(1, sprintf("[+] Done.\n"));
    }

    /**
     * @throws Exception
     */
    public function imageAndDocsLinks()
    {
        $this->verbose(1, sprintf("[+] Re-generating Images symlinks.\n"));
        $linked = array();
        /* Images */
        $imagesDir = $this->publize('Images');
        $this->mkdir($imagesDir);
        $dirs = $this->getImagesDirs();
        foreach ($dirs as $dir) {
            $this->linkFiles($dir, $imagesDir, $linked);
        }
        $this->deleteDeadLinks($imagesDir);

        /* Check for conflicts */
        foreach ($linked as $link => $targetList) {
            if (count($targetList) <= 1) {
                continue;
            }
            $targets = join(', ', $targetList);
            $this->debug(sprintf("WARNING: symlink '%s' has multiple targets: %s\n", $link, $targets));
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }

    /**
     * @throws Exception
     */
    public function clearFileCache()
    {
        $this->verbose(1, sprintf("[+] Clearing cached content.\n"));
        $cacheDir = $this->absolutize('var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'image');
        $this->removeFilesByRegex($cacheDir, '/(?:png|gif|xml|src)$/');
        $this->verbose(1, sprintf("[+] Done.\n"));
    }

    /**
     * @throws Exception
     */
    public function refreshJsVersion()
    {
        $this->verbose(1, sprintf("[+] Incrementing WVERSION.\n"));
        $cmd = sprintf("%s/ank.php --script=refreshjsversion 2>&1", escapeshellarg($this->contextRoot));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $this->debug(join("\n", $output) . "\n");
            throw new Exception(sprintf("Error executing '%s'.", $cmd));
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }


    /**
     * @throws Exception
     */
    public function style()
    {
        $this->verbose(1, sprintf("[+] Recomputing style assets.\n"));
        $cmd = sprintf("%s/ank.php --script=setStyle 2>&1", escapeshellarg($this->contextRoot));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $this->debug(join("\n", $output) . "\n");
            throw new Exception(sprintf("Error executing '%s'.", $cmd));
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }

    /**
     * @throws Exception
     */
    public function unStop()
    {
        $this->verbose(1, sprintf("[+] Removing maintenance mode.\n"));
        $maintenanceFile = $this->absolutize('maintenance.lock');
        if (is_file($maintenanceFile)) {
            if (unlink($maintenanceFile) === false) {
                throw new Exception(sprintf("Error removing file '%s'.", $maintenanceFile));
            }
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }


    public function stop()
    {
        $this->verbose(1, sprintf("[+] Set maintenance mode.\n"));
        $maintenanceFile = $this->absolutize('maintenance.lock');
        if (!is_file($maintenanceFile)) {
            file_put_contents($maintenanceFile, date("c"));
            if (!is_file($maintenanceFile)) {
                throw new Exception(sprintf("Error create file '%s'.", $maintenanceFile));
            }
        }
        $this->verbose(1, sprintf("[+] Http Access Disabled.\n"));
    }

    /**
     * @throws \Dcp\Db\Exception
     */
    public function reapplyDatabaseParameters()
    {
        require_once 'WHAT/Lib.Common.php';
        require_once 'WHAT/autoload.php';

        $this->verbose(1, sprintf("[+] Reapplying database parameters.\n"));

        DbManager::query('SELECT current_database()', $dbName, true, true);
        $paramList = array(
            'DateStyle' => 'ISO, DMY',
            'standard_conforming_strings' => 'off'
        );
        foreach ($paramList as $paramName => $paramValue) {
            $sql = sprintf(
                "ALTER DATABASE %s SET %s = %s",
                pg_escape_identifier($dbName),
                pg_escape_identifier($paramName),
                pg_escape_literal($paramValue)
            );
            DbManager::query($sql, $res, true, true);
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
}
