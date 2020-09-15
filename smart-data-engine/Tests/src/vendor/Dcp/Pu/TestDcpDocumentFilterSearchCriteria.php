<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\SearchCriteria;

class TestDcpDocumentFilterSearchCriteria extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_SEARCHCRITERIA';

    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_SearchCriteria.csv"
        );
    }

    /**
     * @param $test
     * @dataProvider dataSearchCriteria
     */
    public function testSearchCriteria($test)
    {
        $this->common_testFilter(
            $test["fam"],
            new SearchCriteria($test["searchCriteriaValue"]),
            $test["expected"]
        );
    }

    public function dataSearchCriteria()
    {
        return array(
            //Title
            array(
                array(
                    "fam" => self::FAM,
                    "searchCriteriaValue" => array(
                        "kind" => "property",
                        "field" => "title",
                        "operator" => array(
                            "name" => "isEmpty",
                            "flags" => array(),
                        ),
                        "value" => null,
                        "filters" => array(),
                        "logic" => "and"
                    ),
                    "expected" => []
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "searchCriteriaValue" => array(
                        "kind" => "property",
                        "field" => "title",
                        "operator" => array(
                            "name" => "isEmpty",
                            "flags" => array("not"),
                        ),
                        "value" => null,
                        "filters" => array(),
                        "logic" => "and"
                    ),
                    "expected" => [
                        "SEARCHCRITERIA_2",
                    ]
                )
            ),
        );
    }
}
