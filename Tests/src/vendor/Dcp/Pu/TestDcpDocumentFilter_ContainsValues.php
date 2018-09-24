<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;


class TestDcpDocumentFilter_ContainsValues extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_CONTAINSVALUES';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_ContainsValues.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_ContainsValues
     */
    public function test_ContainsValues($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\ContainsValues($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_ContainsValues()
    {
        return array(
            // M_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => "Y",
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => "Y",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => array(
                        "Y",
                        "X"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => array(
                        "B",
                        "A"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            // M_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(array(
                        "FOO_1",
                        "FOO_4"
                    )) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(array(
                        "FOO_2",
                        "FOO_1"
                    )) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(array(
                        "U_1",
                        "U_4"
                    )) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(array(
                        "U_2",
                        "U_1"
                    )) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "Un",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => array(
                        "Un",
                        "Quatre"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => array(
                        "Deux",
                        "Un"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "<b>Un</b>",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => array(
                        "<b>Un</b>",
                        "<b>Quatre</b>"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => array(
                        "<b>Deux</b>",
                        "<b>Un</b>"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "Un\nUn",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => array(
                        "Un\nUn",
                        "Quatre\nQuatre"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => array(
                        "Deux\nDeux",
                        "Un\nUn"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "42",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => array(
                        42,
                        44
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => array(
                        43,
                        42
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "42.42",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => array(
                        42.42,
                        44.44
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => array(
                        43.43,
                        42.42
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "42.42",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => array(
                        42.42,
                        44.44
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => array(
                        43.43,
                        42.42
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "01/01/2014",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => array(
                        "01/01/2014",
                        "30/06/2015"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => array(
                        "31/12/2014",
                        "01/01/2014"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "01/01/2014 12:13",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => array(
                        "01/01/2014 12:13",
                        "30/06/2015 12:13"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => array(
                        "31/12/2014 12:13",
                        "01/01/2014 12:13"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "12:13:14",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => array(
                        "12:13:14",
                        "14:15:16"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => array(
                        "13:14:15",
                        "12:13:14"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => "Y",
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => array(
                        "Y",
                        "X"
                    ) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => array(
                        "B",
                        "A"
                    ) ,
                    "expected" => array(
                        "CONTAINSVALUES_3"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => new LateNameResolver(array(
                        "FOO_1",
                        "FOO_4"
                    )) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOCID",
                    "value" => new LateNameResolver(array(
                        "FOO_2",
                        "FOO_1"
                    )) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
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
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => new LateNameResolver(array(
                        "U_1",
                        "U_4"
                    )) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ACCOUNT",
                    "value" => new LateNameResolver(array(
                        "U_1",
                        "U_2"
                    )) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            // X_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => new LateNameResolver("FOO_1") ,
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => new LateNameResolver(array(
                        "FOO_1",
                        "FOO_4"
                    )) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_DOCID",
                    "value" => new LateNameResolver(array(
                        "FOO_1",
                        "FOO_3"
                    )) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            // X_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => new LateNameResolver("U_1") ,
                    "flags" => \Anakeen\Search\Filters\ContainsValues::NOT,
                    "expected" => array(
                        "CONTAINSVALUES_1",
                        "CONTAINSVALUES_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => new LateNameResolver(array(
                        "U_1",
                        "U_4"
                    )) ,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "X_ACCOUNT",
                    "value" => new LateNameResolver(array(
                        "U_1",
                        "U_3"
                    )) ,
                    "expected" => array(
                        "CONTAINSVALUES_2"
                    )
                )
            ) ,
        );
    }
}
