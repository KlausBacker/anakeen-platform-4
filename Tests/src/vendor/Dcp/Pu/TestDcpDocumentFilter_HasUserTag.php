<?php

namespace Dcp\Pu;


use Anakeen\Core\Account;
use Anakeen\Core\SEManager;

class TestDcpDocumentFilter_HasUserTag extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_HASUSERTAG';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_HasUserTag.ods"
        );
    }
    /*
    protected function setUp()
    {
        parent::setUp();
        $this->localSetup();
    }
    */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::localSetup();
    }
    public static function localSetup()
    {
        /**
         * @var \SmartStructure\Iuser $user
         */
        $uid = self::_uid('U_2');
        $doc = SEManager::getDocument('HASUSERTAG_2');
        $doc->addUTag($uid, 'foo');
        unset($user);
        unset($doc);
        
        $uid = self::_uid('U_3');
        $doc = SEManager::getDocument('HASUSERTAG_3');
        $doc->addUTag($uid, 'FOO');
        unset($user);
        unset($doc);
    }
    private static function _uid($userLogicalName)
    {
        $user = SEManager::getDocument($userLogicalName);
        if (!$user || !$user->isAlive()) {
            return null;
        }
        return Account::getUidFromFid($user->id);
    }
    /**
     * @param $test
     * @dataProvider data_HasApplicationTag
     */
    public function test_HasApplicationTag($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $uid = $this->_uid($test["user"]);
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\HasUserTag($uid, $test["value"]) , $test["expected"]);
    }
    
    public function data_HasApplicationTag()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "user" => "U_1",
                    "value" => "foo",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "user" => "U_2",
                    "value" => "FOO",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "user" => "U_2",
                    "value" => "foo",
                    "expected" => array(
                        "HASUSERTAG_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "user" => "U_3",
                    "value" => "FOO",
                    "expected" => array(
                        "HASUSERTAG_3"
                    )
                )
            )
        );
    }
}
