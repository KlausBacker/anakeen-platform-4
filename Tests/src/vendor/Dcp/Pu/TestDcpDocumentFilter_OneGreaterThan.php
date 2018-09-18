<?php

namespace Dcp\Pu;


class TestDcpDocumentFilter_OneGreaterThan extends TestDcpDocumentFilter_common
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
     * @dataProvider data_OneGreaterThan
     */
    public function test_OneGreaterThan($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneGreaterThan($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_OneGreaterThan()
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
     * @dataProvider data_OneGreaterThanOrEqual
     */
    public function test_OneGreaterThanOrEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneGreaterThanOrEqual($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_OneGreaterThanOrEqual()
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
     * @dataProvider data_OneLesserThan
     */
    public function test_OneLesserThan($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneLesserThan($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_OneLesserThan()
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
     * @dataProvider data_OneLesserThanOrEqual
     */
    public function test_OneLesserThanOrEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneLesserThanOrEqual($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_OneLesserThanOrEqual()
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
