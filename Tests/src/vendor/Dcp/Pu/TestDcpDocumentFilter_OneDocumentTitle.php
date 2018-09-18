<?php

namespace Dcp\Pu;


class TestDcpDocumentFilter_OneDocumentTitle extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ONEDOCUMENTTITLE';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneDocumentTitle.ods"
        );
    }
    protected function setUp()
    {
        parent::setUp();
        $this->localSetup();
    }
    public function localSetup()
    {
        $staticEntries = array();
        \Dcp\Pu\LateNameResolver::setStaticEntries($staticEntries);
    }
    /**
     * @param $test
     * @dataProvider data_OneDocumentTitle
     */
    public function test_OneDocumentTitle($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneDocumentTitle($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_OneDocumentTitle()
    {
        return array(
            // M_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => "Foo Un",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\DocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            // M_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => "Un Uh",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => "Un Uh",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            // A_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => "Foo Un",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            // A_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => "Un Uh",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => "Un Uh",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            // X_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => "Foo Un",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            // X_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => "Un Uh",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => "Un Uh",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\OneDocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\OneDocumentTitle::NOCASE,
                    "expected" => array(
                        "ONEDOCUMENTTITLE_2"
                    )
                )
            )
        );
    }
}
