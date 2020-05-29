<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\OneEmpty;

class TestDcpDocumentFilterOneEmpty extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ONEEMPTY';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneEmpty.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneEmpty
     */
    public function testOneEmpty($test)
    {
        $this->common_testFilter($test["fam"], new OneEmpty($test["attr"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneEmpty()
    {
        return array(
            // M_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "expected" => array(
                        "ONEEMPTY_1",
                        "ONEEMPTY_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
                    "expected" => array(
                        "ONEEMPTY_2",
                        "ONEEMPTY_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
                    "expected" => array(
                        "ONEEMPTY_1",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
                    "expected" => array(
                        "ONEEMPTY_2",
                    )
                )
            ),
            // M_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "expected" => array(
                        "ONEEMPTY_1",
                        "ONEEMPTY_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
                    "expected" => array(
                        "ONEEMPTY_2",
                        "ONEEMPTY_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
                    "expected" => array(
                        "ONEEMPTY_1",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
                    "expected" => array(
                        "ONEEMPTY_2",
                    )
                )
            ),
            // M_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "expected" => array(
                        "ONEEMPTY_1",
                        "ONEEMPTY_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
                    "expected" => array(
                        "ONEEMPTY_2",
                        "ONEEMPTY_3",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
                    "expected" => array(
                        "ONEEMPTY_1",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
                    "expected" => array(
                        "ONEEMPTY_2",
                    )
                )
            ),
            // A_TEXT
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TEXT",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_HTMLTEXT
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_HTMLTEXT",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_HTMLTEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_HTMLTEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_HTMLTEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_LONGTEXT
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_LONGTEXT",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_LONGTEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_LONGTEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_LONGTEXT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_INT
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_INT",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_INT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_INT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_INT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_DOUBLE
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOUBLE",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOUBLE",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOUBLE",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOUBLE",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_MONEY
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_MONEY",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_MONEY",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_MONEY",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_MONEY",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_DATE
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DATE",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DATE",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DATE",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DATE",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_TIMESTAMP
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIMESTAMP",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIMESTAMP",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIMESTAMP",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIMESTAMP",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_TIME
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIME",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIME",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIME",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_TIME",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_ENUM
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ENUM",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ENUM",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ENUM",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ENUM",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_DOCID
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOCID",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOCID",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOCID",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_DOCID",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // A_ACCOUNT
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ACCOUNT",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ACCOUNT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ACCOUNT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "A_ACCOUNT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // X_DOCID
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_DOCID",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_DOCID",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_DOCID",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_DOCID",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
//            // X_ACCOUNT
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_ACCOUNT",
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_ACCOUNT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                        "ONEEMPTY_3",
//                    )
//                )
//            ) ,
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_ACCOUNT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL,
//                    "expected" => array(
//                        "ONEEMPTY_1",
//                    )
//                )
//            ),
//            array(
//                array(
//                    "fam" => self::FAM,
//                    "attr" => "X_ACCOUNT",
//                    "flags" => \Anakeen\Search\Filters\OneEmpty::ALL + OneEmpty::NOT,
//                    "expected" => array(
//                        "ONEEMPTY_2",
//                    )
//                )
//            ),
        );
    }
}
