<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\OneGreaterThan;
use Anakeen\Search\Filters\OneLesserThan;

class TestDcpDocumentFilterOneGreaterThan extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ONEGREATERTHAN';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneGreaterThan.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneGreaterThan
     */
    public function testOneGreaterThan($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneGreaterThan($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneGreaterThan()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "20",
                    "expected" => array(
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "20.5",
                    "expected" => array(
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "20.5",
                    "expected" => array(
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "30/06/2014",
                    "expected" => array(
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "30/06/2014 12:13",
                    "expected" => array(
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "13:14:15",
                    "expected" => array(
                        "ONEGREATERTHAN_4"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneGreaterThanOrEqual
     */
    public function testOneGreaterThanOrEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneGreaterThan($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : OneGreaterThan::EQUAL)), $test["expected"]);
    }
    
    public function dataOneGreaterThanOrEqual()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "20",
                    "expected" => array(
                        "ONEGREATERTHAN_3",
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "20.5",
                    "expected" => array(
                        "ONEGREATERTHAN_3",
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "20.5",
                    "expected" => array(
                        "ONEGREATERTHAN_3",
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "30/06/2014",
                    "expected" => array(
                        "ONEGREATERTHAN_3",
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "30/06/2013 12:13",
                    "expected" => array(
                        "ONEGREATERTHAN_3",
                        "ONEGREATERTHAN_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "13:14:15",
                    "expected" => array(
                        "ONEGREATERTHAN_3",
                        "ONEGREATERTHAN_4"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneLesserThan
     */
    public function testOneLesserThan($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneLesserThan($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneLesserThan()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "10",
                    "expected" => array(
                        "ONEGREATERTHAN_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "10.5",
                    "expected" => array(
                        "ONEGREATERTHAN_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "10.5",
                    "expected" => array(
                        "ONEGREATERTHAN_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "01/01/2014",
                    "expected" => array(
                        "ONEGREATERTHAN_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "01/01/2014 12:13",
                    "expected" => array(
                        "ONEGREATERTHAN_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "12:13:14",
                    "expected" => array(
                        "ONEGREATERTHAN_2"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneLesserThanOrEqual
     */
    public function testOneLesserThanOrEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneLesserThan($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : OneLesserThan::EQUAL)), $test["expected"]);
    }
    
    public function dataOneLesserThanOrEqual()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "10",
                    "expected" => array(
                        "ONEGREATERTHAN_2",
                        "ONEGREATERTHAN_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "10.5",
                    "expected" => array(
                        "ONEGREATERTHAN_2",
                        "ONEGREATERTHAN_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "10.5",
                    "expected" => array(
                        "ONEGREATERTHAN_2",
                        "ONEGREATERTHAN_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "01/01/2014",
                    "expected" => array(
                        "ONEGREATERTHAN_2",
                        "ONEGREATERTHAN_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "01/01/2014 12:13",
                    "expected" => array(
                        "ONEGREATERTHAN_2",
                        "ONEGREATERTHAN_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "12:13:14",
                    "expected" => array(
                        "ONEGREATERTHAN_2",
                        "ONEGREATERTHAN_3"
                    )
                )
            )
        );
    }
}
