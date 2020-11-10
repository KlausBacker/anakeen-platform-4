<?php

namespace Dcp\Pu;

use Anakeen\Core\DbManager;

class TestCaseDcpCommonFamily extends TestCaseDcp
{

    protected function tearDown(): void
    {
        DbManager::rollbackPoint('testunit');
    }

    protected function setUp(): void
    {
        $this->logTest();
        DbManager::savePoint('testunit');
    }
    /**
     * return file to import before run test
     * could be an array if several files
     *
     * @static
     * @return string|array
     */
    protected static function getCommonImportFile()
    {
        return '';
    }

    protected static function getConfigFile()
    {
        return '';
    }

    public static function setUpBeforeClass(): void
 {
        parent::setUpBeforeClass();

        self::connectUser();
        self::beginTransaction();
        \Anakeen\Core\SEManager::cache()->clear();

        $cf = static::getCommonImportFile();
        if ($cf) {
            if (!is_array($cf)) {
                $cf = array(
                    $cf
                );
            }
            foreach ($cf as $f) {
                try {
                    self::importDocument($f);
                } catch (\Anakeen\Exception $e) {
                    self::rollbackTransaction();
                    throw new \Anakeen\Exception(sprintf("Exception while importing file '%s': %s", $f, $e->getMessage()));
                }
            }
        }


        $cf = static::getConfigFile();
        if ($cf) {
            if (!is_array($cf)) {
                $cf = array($cf);
            }
            foreach ($cf as $f) {
                self::importConfigurationFile($f);
            }
        }
    }

    public static function importConfigurationFile($f)
    {
        try {
            static::importConfiguration($f);
        } catch (\Anakeen\Exception $e) {
            self::rollbackTransaction();
            throw new \Anakeen\Exception(sprintf("Exception while importing file '%s': %s", $f, $e->getMessage()));
        }
    }


    public static function importAccountFile($f)
    {
        try {
            $import = new \Anakeen\Exchange\ImportAccounts();
            $import->setFile($f);
            $import->import();
        } catch (\Anakeen\Exception $e) {
            self::rollbackTransaction();
            throw new \Anakeen\Exception(sprintf("Exception while importing file '%s': %s", $f, $e->getMessage()));
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::rollbackTransaction();
    }
}
