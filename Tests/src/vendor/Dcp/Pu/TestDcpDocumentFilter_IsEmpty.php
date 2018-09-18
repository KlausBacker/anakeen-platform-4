<?php

namespace Dcp\Pu;


class TestDcpDocumentFilter_IsEmpty extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IsEmpty.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_IsEmpty
     */
    public function test_IsEmpty($test)
    {
        $s = new \SearchDoc("", $test["fam"]);
        $s->addFilter(new \Anakeen\Search\Filters\IsEmpty($test["attr"]));
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
    
    public function data_IsEmpty()
    {
        return array(
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_TEXT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_HTMLTEXT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_LONGTEXT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_INT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_DOUBLE",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_MONEY",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_DATE",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_TIMESTAMP",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_TIME",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_ENUM",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_DOCID",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "S_ACCOUNT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "M_ENUM",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "M_DOCID",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "M_ACCOUNT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_TEXT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_HTMLTEXT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_LONGTEXT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_INT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_DOUBLE",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_MONEY",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_DATE",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_TIMESTAMP",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_TIME",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_ENUM",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_DOCID",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => "TEST_DCP_DOCUMENTFILTER_ISEMPTY",
                    "attr" => "A_ACCOUNT",
                    "expected" => array(
                        "ISEMPTY_1"
                    )
                )
            )
        );
    }
}
