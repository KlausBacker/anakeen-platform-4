<?php

namespace Dcp\Pu;

class TestDcpDocumentFilterNameEquals extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_NAMEEQUALS';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_NameEquals.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataNameEquals
     */
    public function testNameEquals($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\NameEquals($test["value"]), $test["expected"]);
    }
    
    public function dataNameEquals()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "NAMEEQUALS_2",
                    "expected" => array(
                        "NAMEEQUALS_2"
                    )
                )
            )
        );
    }
}
