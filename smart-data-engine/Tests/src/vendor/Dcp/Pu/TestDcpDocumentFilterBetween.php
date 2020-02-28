<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\Between;

class TestDcpDocumentFilterBetween extends TestDcpDocumentFiltercommon
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_Between.ods"
        );
    }

    /**
     * @param $test
     * @dataProvider dataBetween
     */
    public function testBetween($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\Between(
            $test["attr"],
            $test["value"],
            (isset($test["flags"]) ? $test["flags"] : 0)
        ), $test["expected"]);
    }

    public function dataBetween()
    {
        //INT
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_INT",
                    "value" => array(
                        "5",
                        "11"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_INT",
                    "value" => array(
                        "11",
                        "5"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_INT",
                    "flags"=> Between::EQUALLEFT,
                    "value" => array(
                        "5",
                        "11"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_INT",
                    "flags"=> Between::EQUALRIGHT,
                    "value" => array(
                        "5",
                        "10"
                    ),
                    "expected" => array(
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_INT",
                    "flags"=> Between::EQUALLEFT + Between::EQUALRIGHT,
                    "value" => array(
                        "5",
                        "10"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_INT",
                    "flags"=> Between::NOT,
                    "value" => array(
                        "5",
                        "11"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_4",
                    )
                )
            ),
            // DOUBLE
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DOUBLE",
                    "value" => array(
                        "5.5",
                        "11.11"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DOUBLE",
                    "value" => array(
                        "11.11",
                        "5.5"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DOUBLE",
                    "flags"=> Between::EQUALLEFT,
                    "value" => array(
                        "5.5",
                        "11.11"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DOUBLE",
                    "flags"=> Between::EQUALRIGHT,
                    "value" => array(
                        "5.5",
                        "10.50"
                    ),
                    "expected" => array(
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DOUBLE",
                    "flags"=> Between::EQUALLEFT + Between::EQUALRIGHT,
                    "value" => array(
                        "5.5",
                        "10.50"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DOUBLE",
                    "flags"=> Between::NOT,
                    "value" => array(
                        "5.5",
                        "11.11"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_4",
                    )
                )
            ),
            // MONEY
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_MONEY",
                    "value" => array(
                        "5.5",
                        "11.11"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_MONEY",
                    "value" => array(
                        "11.11",
                        "5.5"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_MONEY",
                    "flags"=> Between::EQUALLEFT,
                    "value" => array(
                        "5.5",
                        "11.11"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_MONEY",
                    "flags"=> Between::EQUALRIGHT,
                    "value" => array(
                        "5.5",
                        "10.50"
                    ),
                    "expected" => array(
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_MONEY",
                    "flags"=> Between::EQUALLEFT + Between::EQUALRIGHT,
                    "value" => array(
                        "5.5",
                        "10.50"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_MONEY",
                    "flags"=> Between::NOT,
                    "value" => array(
                        "5.5",
                        "11.11"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_4",
                    )
                )
            ),
            // Date
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DATE",
                    "value" => array(
                        "01/01/1970",
                        "01/06/2014"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DATE",
                    "value" => array(
                        "01/06/2014",
                        "01/01/1970"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DATE",
                    "flags"=> Between::EQUALLEFT,
                    "value" => array(
                        "01/01/1970",
                        "01/06/2014"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DATE",
                    "flags"=> Between::EQUALRIGHT,
                    "value" => array(
                        "01/01/1970",
                        "01/01/2014"
                    ),
                    "expected" => array(
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DATE",
                    "flags"=> Between::EQUALLEFT + Between::EQUALRIGHT,
                    "value" => array(
                        "01/01/1970",
                        "01/01/2014"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_DATE",
                    "flags"=> Between::NOT,
                    "value" => array(
                        "01/01/1970",
                        "01/06/2014"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_4",
                    )
                )
            ),
            // Timestamp
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIMESTAMP",
                    "value" => array(
                        "01/01/1970 13:14:15",
                        "01/06/2014 13:14:15"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIMESTAMP",
                    "value" => array(
                        "01/06/2014 13:14:15",
                        "01/01/1970 13:14:15"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIMESTAMP",
                    "flags"=> Between::EQUALLEFT,
                    "value" => array(
                        "01/01/1970 13:14",
                        "01/06/2014 13:14:15"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIMESTAMP",
                    "flags"=> Between::EQUALRIGHT,
                    "value" => array(
                        "01/01/1970 13:14",
                        "01/01/2014 13:14"
                    ),
                    "expected" => array(
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIMESTAMP",
                    "flags"=> Between::EQUALLEFT + Between::EQUALRIGHT,
                    "value" => array(
                        "01/01/1970 13:14",
                        "01/01/2014 13:14"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIMESTAMP",
                    "flags"=> Between::NOT,
                    "value" => array(
                        "01/01/1970 13:14:15",
                        "01/06/2014 13:14:15"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_4",
                    )
                )
            ),
            // Time
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIME",
                    "value" => array(
                        "06:30:00",
                        "12:30:00"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIME",
                    "value" => array(
                        "12:30:00",
                        "06:30:00"
                    ),
                    "expected" => array(
                        "BETWEEN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIME",
                    "flags"=> Between::EQUALLEFT,
                    "value" => array(
                        "06:30:00",
                        "12:30:00"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIME",
                    "flags"=> Between::EQUALRIGHT,
                    "value" => array(
                        "06:30:00",
                        "10:30:00"
                    ),
                    "expected" => array(
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIME",
                    "flags"=> Between::EQUALLEFT + Between::EQUALRIGHT,
                    "value" => array(
                        "06:30:00",
                        "10:30:00"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_BETWEEN",
                    "attr" => "S_TIME",
                    "flags"=> Between::NOT,
                    "value" => array(
                        "06:30:00",
                        "12:30:00"
                    ),
                    "expected" => array(
                        "BETWEEN_2",
                        "BETWEEN_4",
                    )
                )
            ),
        );
    }
}
