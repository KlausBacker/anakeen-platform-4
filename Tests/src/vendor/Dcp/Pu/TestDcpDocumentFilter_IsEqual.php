<?php

namespace Dcp\Pu;


class TestDcpDocumentFilter_IsEqual extends TestDcpDocumentFilter_common
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IsEqual.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_isEqual
     */
    public function test_IsEqual($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter(
            $test["fam"],
            new \Anakeen\Search\Filters\IsEqual($test["attr"], $test["value"]),
            $test["expected"]
        );
    }
    
    public function data_IsEqual()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_TEXT",
                    "value" => "Non vide",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_HTMLTEXT",
                    "value" => "<b>Non vide</b>",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_LONGTEXT",
                    "value" => "Non\nvide",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_INT",
                    "value" => "42",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_DOUBLE",
                    "value" => "42.42",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_MONEY",
                    "value" => "42.42",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_DATE",
                    "value" => "31/12/2014",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_TIMESTAMP",
                    "value" => "31/12/2014 13:14:00",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_TIME",
                    "value" => "13:14:15",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_ENUM",
                    "value" => "N",
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_DOCID",
                    "value" => new LateNameResolver("FOO_1"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "S_ACCOUNT",
                    "value" => new LateNameResolver("U_1"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "M_ENUM",
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(array("FOO_1", "FOO_2")),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(array("U_1", "U_1")),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_TEXT",
                    "value" => array("Un", "Deux"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_HTMLTEXT",
                    "value" => array("<b>Un</b>", "<b>Deux</b>"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_LONGTEXT",
                    "value" => array("Un\nUn", "Deux\nDeux"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_INT",
                    "value" => array(42, 43),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_DOUBLE",
                    "value" => array(42.42, 43.43),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_MONEY",
                    "value" => array(42.42, 43.43),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_DATE",
                    "value" => array("01/01/2014", "31/12/2014"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_TIMESTAMP",
                    "value" => array("01/01/2014 12:13:00", "31/12/2014 12:13:00"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_TIME",
                    "value" => array("12:13:14", "13:14:15"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_ENUM",
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_DOCID",
                    "value" => new LateNameResolver(array("FOO_1", "FOO_2")),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEQUAL",
                    "attr" => "A_ACCOUNT",
                    "value" => new LateNameResolver(array("U_1", "U_1")),
                    "expected" => array(
                        "ISEQUAL_2"
                    )
                )
            )
        );
    }
}
