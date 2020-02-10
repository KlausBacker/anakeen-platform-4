<?php

namespace Dcp\Pu;

class TestDcpDocumentFilterContains extends TestDcpDocumentFiltercommon
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_Contains.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataContains
     */
    public function testContains($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\Contains($test["attr"], $test["value"]), $test["expected"]);
    }
    
    public function dataContains()
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
     * @dataProvider dataContainsNoCase
     */
    public function testContainsNoCase($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\Contains($test["attr"], $test["value"], \Anakeen\Search\Filters\Contains::NOCASE), $test["expected"]);
    }
    public function dataContainsNoCase()
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
    /**
     * @param $test
     * @dataProvider dataContainsNoDiacritic
     */
    public function testContainsNoDiacritic($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter(
            $test["fam"],
            new \Anakeen\Search\Filters\Contains(
                $test["attr"],
                $test["value"],
                \Anakeen\Search\Filters\Contains::NODIACRITIC
            ),
            $test["expected"]
        );
    }
    public function dataContainsNoDiacritic()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_TEXT",
                    "value" => "hete",
                    "expected" => array(
                        "CONTAINS_5"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_LONGTEXT",
                    "value" => "hete",
                    "expected" => array(
                        "CONTAINS_5"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_CONTAINS",
                    "attr" => "S_HTMLTEXT",
                    "value" => "hete",
                    "expected" => array(
                        "CONTAINS_5"
                    )
                )
            )
        );
    }
}
