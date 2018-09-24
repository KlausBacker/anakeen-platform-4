<?php

namespace Dcp\Pu;

class TestDcpDocumentFilter_Contains extends TestDcpDocumentFilter_common
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_Contains.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_Contains
     */
    public function test_Contains($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\Contains($test["attr"], $test["value"]) , $test["expected"]);
    }
    
    public function data_Contains()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_TEXT",
                    "value" => "rouge",
                    "expected" => array(
                        "CONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_LONGTEXT",
                    "value" => "rouge",
                    "expected" => array(
                        "CONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_HTMLTEXT",
                    "value" => "rouge",
                    "expected" => array(
                        "CONTAINS_2"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider data_ContainsNoCase
     */
    public function test_ContainsNoCase($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\Contains($test["attr"], $test["value"], \Anakeen\Search\Filters\Contains::NOCASE) , $test["expected"]);
    }
    public function data_ContainsNoCase()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_TEXT",
                    "value" => "rouge",
                    "expected" => array(
                        "CONTAINS_2",
                        "CONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_LONGTEXT",
                    "value" => "rouge",
                    "expected" => array(
                        "CONTAINS_2",
                        "CONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_HTMLTEXT",
                    "value" => "rouge",
                    "expected" => array(
                        "CONTAINS_2",
                        "CONTAINS_3"
                    )
                )
            )
        );
    }
}
