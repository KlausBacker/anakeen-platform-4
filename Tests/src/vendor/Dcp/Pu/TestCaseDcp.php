<?php

namespace Dcp\Pu;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;

class TestCaseDcp extends \PHPUnit\Framework\TestCase
{
    /**
     * DbAccess string
     *
     * @var string
     */
    protected static $dbaccess;

    /**
     * User keep in cache during the sudo
     *
     * @var \Anakeen\Core\Account
     */
    protected static $user = null;
    /**
     * Store original include_path
     */
    protected static $include_path = null;

    protected static $testDirectory = __DIR__;

    protected static $importCsvEnclosure = "auto";
    protected static $importCsvSeparator = "auto";

    protected function setUp()
    {
        $this->log(sprintf("========== %s ========", $this->getName()));
        $this->connectUser("admin");

        $this->beginTransaction();
    }

    protected function tearDown()
    {
        $this->rollbackTransaction();
    }

    public static function setUpBeforeClass()
    {
    }

    public static function log($text)
    {
        file_put_contents(CoreTests::LOGFILE, sprintf("[%s] %s\n", date("Y-m-d H:i:s"), $text), FILE_APPEND);
    }

    /**
     * Make a begin in the db
     *
     * @return void
     */
    protected static function beginTransaction()
    {
        DbManager::savePoint('putransaction');
    }

    /**
     *  Make a rollback in the db
     *
     * @return void
     */
    protected static function rollbackTransaction()
    {

        DbManager::rollbackPoint('putransaction');
    }

    /**
     * Connect as a user
     *
     * @param string $login login of the user
     *
     * @return void
     */
    protected static function connectUser($login = "admin")
    {
        $action = ContextManager::getCurrentAction();
        if (!$action) {
            $u = new \Anakeen\Core\Account();
            $u->setLoginName($login);
            \Anakeen\Core\ContextManager::initContext($u);
        }
    }

    /**
     * Current action
     *
     * @return \Anakeen\Core\Internal\Action
     */
    protected static function &getAction()
    {
        $action = ContextManager::getCurrentAction();
        if (!$action) {
            self::connectUser();
        }
        $action = ContextManager::getCurrentAction();
        if (!$action->dbid) {
            if (!$action->dbid) {
                $action->init_dbid();
                if (!$action->dbid) {
                    error_log(__METHOD__ . "lost action dbid");
                }
            }
            $action->init_dbid();
        }
        return $action;
    }

    /**
     * Current application
     *
     * @return \Anakeen\Core\Internal\Application
     */
    protected static function getApplication()
    {
        return ContextManager::getCurrentApplication();
    }

    /**
     * return a single value from DB
     *
     * @param string $sql a query with a single fields in from part
     *
     * @return string
     */
    protected function _DBGetValue($sql)
    {
        DbManager::query($sql, $sval, true, true);
        return $sval;
    }

    /**
     * reset shared documents
     *
     * @return void
     */
    protected static function resetDocumentCache()
    {
        \Anakeen\Core\DocManager::cache()->clear();
    }

    /**
     * use another user
     *
     * @param string $login
     *
     * @return \Anakeen\Core\Account
     * @throws \Dcp\Exception
     */
    protected static function sudo($login)
    {
        $u = new \Anakeen\Core\Account(self::$dbaccess);
        if (!$u->setLoginName($login)) {
            throw new \Dcp\Exception("login $login not exist");
        }

        self::$user = ContextManager::getCurrentUser();


        ContextManager::sudo($u);
        self::resetDocumentCache();
        return $u;
    }

    /**
     * exit sudo
     *
     * @return void
     */
    protected static function exitSudo()
    {
        if (self::$user) {
            ContextManager::sudo(self::$user);
            self::$user = null;
        }
    }

    /**
     * Import a file document description
     *
     * @param string|string[] $file file path
     *
     * @return array
     * @throws \Dcp\Exception
     */
    protected static function importDocument($file)
    {
        if (is_array($file)) {
            return self::importDocuments($file);
        }

        $realfile = $file;
        if (!file_exists($realfile)) {
            $ext = substr($file, strrpos($file, '.') + 1);
            if ($ext == "ods" || $ext == "csv") {
                $realfile = static::$testDirectory . "/" . $file;
            } else {
                $realfile = static::$testDirectory . "/Layout/" . $file;
            }
        }
        if (!file_exists($realfile)) {
            throw new \Dcp\Exception(sprintf("File '%s' not found in '%s'.", $file, $realfile));
        }
        $oImport = new \ImportDocument();
        $oImport->setCsvOptions(static::$importCsvSeparator, static::$importCsvEnclosure);
        //error_log(__METHOD__."import $realfile");
        $oImport->setVerifyAttributeAccess(false);
        $cr = $oImport->importDocuments(self::getAction(), $realfile);
        $err = $oImport->getErrorMessage();
        if ($err) {
            throw new \Dcp\Exception($err);
        }
        return $cr;
    }

    /**
     * Import multiple files specified as a array list
     *
     * @param array $fileList list of files to import
     *
     * @return array
     */
    protected static function importDocuments($fileList)
    {
        $cr = array();
        if (!is_array($fileList)) {
            $cr[] = self::importDocument($fileList);
            return $cr;
        }

        foreach ($fileList as $file) {
            $cr[] = self::importDocument($file);
        }
        return $cr;
    }

    /**
     * Import CSV data
     *
     * @param string $data CSV data
     *
     * @throws \Dcp\Exception
     */
    public function importCsvData($data)
    {
        $tmpFile = tempnam(ContextManager::getTmpDir(), "importData");
        if ($tmpFile === false) {
            throw new \Dcp\Exception(sprintf("Error creating temporary file in '%s'.", ContextManager::getTmpDir()));
        }
        $ret = rename($tmpFile, $tmpFile . '.csv');
        if ($ret === false) {
            throw new \Dcp\Exception(sprintf("Error renaming '%s' to '%s'.", $tmpFile, $tmpFile . '.csv'));
        }
        $tmpFile = $tmpFile . '.csv';
        $ret = file_put_contents($tmpFile, $data);
        if ($ret === false) {
            throw new \Dcp\Exception(sprintf("Error writing to file '%s'.", $tmpFile));
        }
        $this->importDocument($tmpFile);
        unlink($tmpFile);
    }

    /**
     * Set the include_path INI parameter
     *
     * @param string $include_path the new include_path to use
     */
    public static function setIncludePath($include_path)
    {
        if (self::$include_path == null) {
            self::$include_path = ini_get('include_path');
        }
        ini_set('include_path', $include_path);
    }

    /**
     * Set back the original include_path INI parameter
     */
    public static function resetIncludePath()
    {
        if (self::$include_path !== null) {
            ini_set('include_path', self::$include_path);
        }
    }

    /**
     * Mark test as incomplete (skip) if a core param is not equal
     * to the required value.
     *
     * @param string $paramName          the core parameter name
     * @param string $requiredValue      the required value
     * @param bool   $markTestIncomplete automatically calls the markTestIncomplete() method if the value is different
     *
     * @return bool
     */
    public function requiresCoreParamEquals($paramName, $requiredValue, $markTestIncomplete = true)
    {
        $value = ContextManager::getApplicationParam($paramName, '');
        if ($value === $requiredValue) {
            return true;
        }
        if ($markTestIncomplete === true) {
            $this->markTestIncomplete(sprintf("Test requires %s '%s' (found '%s').", $paramName, $requiredValue, $value));
        }
        return false;
    }
}
