<?php

namespace Dcp\Pu;

class TestDcpDocumentFilter_IsLesser extends TestDcpDocumentFilter_common
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IsLesser.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_IsLesser
     */
    public function test_IsLesser($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\IsLesser($test["attr"], $test["value"]) , $test["expected"]);
    }
    
    public function data_IsLesser()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_INT",
                    "value" => "10",
                    "expected" => array(
                        "ISLESSER_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_DOUBLE",
                    "value" => "10.5",
                    "expected" => array(
                        "ISLESSER_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_MONEY",
                    "value" => "10.5",
                    "expected" => array(
                        "ISLESSER_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_TIMESTAMP",
                    "value" => "01/01/2014 13:14:15",
                    "expected" => array(
                        "ISLESSER_2",
                        "ISLESSER_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_TIME",
                    "value" => "10:30:00",
                    "expected" => array(
                        "ISLESSER_2"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider data_IsLesserOrEqual
     */
    public function test_IsLesserOrEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\IsLesser($test["attr"], $test["value"], \Anakeen\Search\Filters\IsLesser::EQUAL) , $test["expected"]);
    }
    
    public function data_IsLesserOrEqual()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_INT",
                    "value" => "10",
                    "expected" => array(
                        "ISLESSER_2",
                        "ISLESSER_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_DOUBLE",
                    "value" => "10.5",
                    "expected" => array(
                        "ISLESSER_2",
                        "ISLESSER_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_MONEY",
                    "value" => "10.5",
                    "expected" => array(
                        "ISLESSER_2",
                        "ISLESSER_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_TIMESTAMP",
                    "value" => "01/01/2014 13:14:15",
                    "expected" => array(
                        "ISLESSER_2",
                        "ISLESSER_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISLESSER",
                    "attr" => "S_TIME",
                    "value" => "10:30:00",
                    "expected" => array(
                        "ISLESSER_2",
                        "ISLESSER_3"
                    )
                )
            )
        );
    }
}
