<?php

namespace Dcp\Pu;

use Anakeen\Core\DbManager;

class TestCaseDcpCommonFamily extends TestCaseDcp
{

    protected function tearDown()
    {
        DbManager::rollbackPoint('testunit');
    }

    protected function setUp()
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

    public static function setUpBeforeClass()
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
                } catch (\Dcp\Exception $e) {
                    self::rollbackTransaction();
                    throw new \Dcp\Exception(sprintf("Exception while importing file '%s': %s", $f, $e->getMessage()));
                }
            }
        }


        $cf = static::getConfigFile();
        if ($cf) {
            if (!is_array($cf)) {
                $cf = array(
                    $cf
                );
            }
            foreach ($cf as $f) {
                try {
                    self::importConfiguration($f);
                } catch (\Dcp\Exception $e) {
                    self::rollbackTransaction();
                    throw new \Dcp\Exception(sprintf("Exception while importing file '%s': %s", $f, $e->getMessage()));
                }
            }
        }
    }

    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
    }
}
