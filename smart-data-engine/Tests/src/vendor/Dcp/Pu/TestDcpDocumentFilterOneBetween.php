<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\OneBetween;

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
            //Empty Value
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => array(null, null),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
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
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "flags" => OneBetween::NOT,
                    "value" => array(
                        0,
                        3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "flags" => OneBetween::ALL,
                    "value" => array(
                        0,
                        3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "flags" => OneBetween::LEFTEQUAL,
                    "value" => array(
                        2,
                        3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "flags" => OneBetween::RIGHTEQUAL,
                    "value" => array(
                        2,
                        3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            // A_DOUBLE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => array(
                        0.0,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "flags" => OneBetween::NOT,
                    "value" => array(
                        0.0,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "flags" => OneBetween::ALL,
                    "value" => array(
                        0.0,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "flags" => OneBetween::LEFTEQUAL,
                    "value" => array(
                        2.2,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "flags" => OneBetween::RIGHTEQUAL,
                    "value" => array(
                        2.2,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            // A_MONEY
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => array(
                        0.0,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "flags" => OneBetween::NOT,
                    "value" => array(
                        0.0,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "flags" => OneBetween::ALL,
                    "value" => array(
                        0.0,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "flags" => OneBetween::LEFTEQUAL,
                    "value" => array(
                        2.2,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "flags" => OneBetween::RIGHTEQUAL,
                    "value" => array(
                        2.2,
                        3.3,
                    ),
                    "expected" => array(
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            // A_DATE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => array(
                        "2000-01-01",
                        "2003-03-03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "flags" => OneBetween::NOT,
                    "value" => array(
                        "2000-01-01",
                        "2003-03-03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "flags" => OneBetween::ALL,
                    "value" => array(
                        "2000-01-01",
                        "2003-03-03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "flags" => OneBetween::LEFTEQUAL,
                    "value" => array(
                        "2002-02-02",
                        "2003-03-03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "flags" => OneBetween::RIGHTEQUAL,
                    "value" => array(
                        "2002-02-02",
                        "2003-03-03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            // A_TIMESTAMP
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => array(
                        "2000-01-01 01:01:01",
                        "2003-03-03 03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "flags" => OneBetween::NOT,
                    "value" => array(
                        "2000-01-01 01:01:01",
                        "2003-03-03 03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "flags" => OneBetween::ALL,
                    "value" => array(
                        "2000-01-01 01:01:01",
                        "2003-03-03 03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "flags" => OneBetween::LEFTEQUAL,
                    "value" => array(
                        "2002-02-02 02:02:02",
                        "2003-03-03 03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "flags" => OneBetween::RIGHTEQUAL,
                    "value" => array(
                        "2002-02-02 02:02:02",
                        "2003-03-03 03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            // A_TIME
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => array(
                        "01:01:01",
                        "03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "flags" => OneBetween::NOT,
                    "value" => array(
                        "01:01:01",
                        "03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_1",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "flags" => OneBetween::ALL,
                    "value" => array(
                        "00:00:01",
                        "03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "flags" => OneBetween::LEFTEQUAL,
                    "value" => array(
                        "02:02:02",
                        "03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_2",
                        "ONEBETWEEN_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "flags" => OneBetween::RIGHTEQUAL,
                    "value" => array(
                        "02:02:02",
                        "03:03:03",
                    ),
                    "expected" => array(
                        "ONEBETWEEN_3",
                        "ONEBETWEEN_4",
                    )
                )
            ) ,
        );
    }
}
