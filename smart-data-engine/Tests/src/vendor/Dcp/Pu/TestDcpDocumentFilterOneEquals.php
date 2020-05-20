<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\OneEquals;

class TestDcpDocumentFilterOneEquals extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ONEEQUALS';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneEquals.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneEquals
     */
    public function testOneEquals($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new OneEquals($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneEquals()
    {
        return array(
            // M_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => "Y",
                    "expected" => array(
                        "ONEEQUALS_2",
                        "ONEEQUALS_4",
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => "Y",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => "Y",
                    "flags" => \Anakeen\Search\Filters\OneEquals::ALL,
                    "expected" => array(
                        "ONEEQUALS_4",
                    )
                )
            ),
            // M_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // M_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_TEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "Un",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "Un",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_HTMLTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "<b>Un</b>",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "<b>Un</b>",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_LONGTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "Un\nUn",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "Un\nUn",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_INT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "42",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "42",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_DOUBLE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "42.42",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "42.42",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_MONEY
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "42.42",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "42.42",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_DATE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "01/01/2014",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "01/01/2014",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_TIMESTAMP
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "01/01/2014 12:13",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "01/01/2014 12:13",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_TIME
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "12:13:14",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "12:13:14",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => "Y",
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => "Y",
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // A_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // X_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => new LateNameResolver("FOO_1"),
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => new LateNameResolver("FOO_1"),
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
            // X_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => new LateNameResolver("U_1"),
                    "expected" => array(
                        "ONEEQUALS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => new LateNameResolver("U_1"),
                    "flags" => \Anakeen\Search\Filters\OneEquals::NOT,
                    "expected" => array(
                        "ONEEQUALS_1",
                        "ONEEQUALS_3"
                    )
                )
            ) ,
        );
    }
}
