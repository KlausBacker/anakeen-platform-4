<?php

namespace Dcp\Pu;

class TestDcpDocumentFilterOneBetween extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_OneBetween';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneBetween.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneBetween
     */
    public function testOneBetween($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneBetween($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneBetween()
    {
        return array(
            // A_INT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => array(
                        0,
                        3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
        );
    }
}
