<?php

namespace Dcp\Pu;


class TestDcpDocumentFilter_IsNotEmpty extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IsNotEmpty.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_IsNotEmpty
     */
    public function test_IsNotEmpty($test)
    {
        $s = new \SearchDoc("", $test["fam"]);
        $s->addFilter(new \Anakeen\Search\Filters\IsNotEmpty($test["attr"]));
        $s->setObjectReturn(false);
        $res = $s->search();
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search returned with error: %s", $err));
        
        $found = array();
        foreach ($res as & $r) {
            $found[] = $r["name"];
        }
        unset($r);
        
        $missing = array_diff($test["expected"], $found);
        $this->assertEmpty($missing, sprintf("Missing elements in result: %s", join(", ", $missing)));
        
        $spurious = array_diff($found, $test["expected"]);
        $this->assertEmpty($spurious, sprintf("Spurious elements in result: %s", join(", ", $spurious)));
    }
    
    public function data_IsNotEmpty()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_TEXT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_HTMLTEXT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_LONGTEXT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_INT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_DOUBLE",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_MONEY",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_DATE",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_TIMESTAMP",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_TIME",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_ENUM",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_DOCID",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "S_ACCOUNT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "M_ENUM",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "M_DOCID",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "M_ACCOUNT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_TEXT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_HTMLTEXT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_LONGTEXT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_INT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_DOUBLE",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_MONEY",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_DATE",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_TIMESTAMP",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_TIME",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_ENUM",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_DOCID",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISNOTEMPTY",
                    "attr" => "A_ACCOUNT",
                    "expected" => array(
                        "ISNOTEMPTY_2"
                    )
                )
            )
        );
    }
}
