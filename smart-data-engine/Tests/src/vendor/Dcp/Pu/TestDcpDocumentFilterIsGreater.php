<?php

namespace Dcp\Pu;

class TestDcpDocumentFilterIsGreater extends TestDcpDocumentFiltercommon
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IsGreater.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataIsGreater
     */
    public function testIsGreater($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\IsGreater($test["attr"], $test["value"]), $test["expected"]);
    }
    
    public function dataIsGreater()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_INT",
                    "value" => "10",
                    "expected" => array(
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_DOUBLE",
                    "value" => "10.5",
                    "expected" => array(
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_MONEY",
                    "value" => "10.5",
                    "expected" => array(
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_TIMESTAMP",
                    "value" => "01/01/2014 13:14:15",
                    "expected" => array(
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_TIME",
                    "value" => "10:30:00",
                    "expected" => array(
                        "ISGREATER_4"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider dataIsGreaterOrEqual
     */
    public function testIsGreaterOrEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\IsGreater($test["attr"], $test["value"], \Anakeen\Search\Filters\IsGreater::EQUAL), $test["expected"]);
    }
    
    public function dataIsGreaterOrEqual()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_INT",
                    "value" => "10",
                    "expected" => array(
                        "ISGREATER_3",
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_DOUBLE",
                    "value" => "10.5",
                    "expected" => array(
                        "ISGREATER_3",
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_MONEY",
                    "value" => "10.5",
                    "expected" => array(
                        "ISGREATER_3",
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_TIMESTAMP",
                    "value" => "01/01/2014 13:14:15",
                    "expected" => array(
                        "ISGREATER_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISGREATER",
                    "attr" => "S_TIME",
                    "value" => "10:30:00",
                    "expected" => array(
                        "ISGREATER_3",
                        "ISGREATER_4"
                    )
                )
            )
        );
    }
}
