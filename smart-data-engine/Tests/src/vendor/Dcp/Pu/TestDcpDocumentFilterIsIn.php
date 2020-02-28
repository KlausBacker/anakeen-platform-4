<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Search\Filters\IsIn;

class TestDcpDocumentFilterIsIn extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ISIN';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IsIn.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataIsIn
     */
    public function testIsIn($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new IsIn($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataIsIn()
    {
        return array(
            // M_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => "Y",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => ["Y", "N"],
                    "expected" => array(
                        "ISIN_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => ["Y", "N"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => ["Y", "X"],
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => ["Y", "X"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => ["B", "A"],
                    "expected" => array(
                        "ISIN_3",
                    )
                )
            ),

            // DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver("FOO_1"),
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => IsIn::NOT,
                    "value" => new LateNameResolver("FOO_1"),
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(["FOO_1", "FOO_2"]),
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(["FOO_1", "FOO_2"]),
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(["FOO_2", "FOO_3", "FOO_1"]),
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(["FOO_4", "FOO_2", "FOO_3"]),
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver("U_1"),
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => IsIn::NOT,
                    "value" => new LateNameResolver("U_1"),
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(["U_1", "U_2"]),
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(["U_1", "U_2"]),
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(["U_1", "U_3", "U_2"]),
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(["U_4", "U_3", "U_2"]),
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // TEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "Un",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "flags" => IsIn::NOT,
                    "value" => "Un",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => ["Un", "Deux"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => ["Un", "Deux"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => ["Un", "Deux", "Trois"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => ["Quatre", "Deux", "Trois"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // HTMLTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "<b>Un</b>",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "flags" => IsIn::NOT,
                    "value" => "<b>Un</b>",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => ["<b>Un</b>", "<b>Deux</b>"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => ["<b>Un</b>", "<b>Deux</b>"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => ["<b>Un</b>", "<b>Trois</b>", "<b>Deux</b>"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => ["<b>Quatre</b>", "<b>Trois</b>", "<b>Deux</b>"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // LONGTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "Un\nUn",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "flags" => IsIn::NOT,
                    "value" => "Un\nUn",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => ["Un\nUn", "Deux\nDeux"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => ["Un\nUn", "Deux\nDeux"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => ["Trois\nTrois", "Un\nUn", "Deux\nDeux"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => ["Trois\nTrois", "Quatre\nQuatre", "Deux\nDeux"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // INT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => "42",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "flags" => IsIn::NOT,
                    "value" => "42",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => ["42", "43"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => ["42", "43"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => ["42", "44", "43"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_INT",
                    "value" => ["45", "44", "43"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // DOUBLE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "42.42",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "flags" => IsIn::NOT,
                    "value" => "42.42",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => ["42.42", "43.43"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => ["42.42", "43.43"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => [42.42, "44.44", "43.43"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => ["45.45", "44.44", "43.43"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // MONEY
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => "42.42",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "flags" => IsIn::NOT,
                    "value" => "42.42",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => ["42.42", "43.43"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => ["42.42", "43.43"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => [42.42, "44.44", "43.43"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_MONEY",
                    "value" => ["45.45", "44.44", "43.43"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // DOUBLE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => "42.42",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "flags" => IsIn::NOT,
                    "value" => "42.42",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => ["42.42", "43.43"],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => ["42.42", "43.43"],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => [42.42, "44.44", "43.43"],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DOUBLE",
                    "value" => ["45.45", "44.44", "43.43"],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // DATE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => "01/01/2014",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "flags" => IsIn::NOT,
                    "value" => "01/01/2014",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => [
                        "01/01/2014",
                        "31/12/2014",
                    ],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => [
                        "01/01/2014",
                        "31/12/2014",
                    ],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => [
                        "01/01/2014",
                        "31/12/2014",
                        "30/06/2015",
                    ],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_DATE",
                    "value" => [
                        "02/03/2014",
                        "31/12/2014",
                        "30/06/2015",
                    ],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // TIMESTAMP
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => "01/01/2014 12:13",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "flags" => IsIn::NOT,
                    "value" => "01/01/2014 12:13",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => [
                        "01/01/2014 12:13",
                        "31/12/2014 12:13",
                    ],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => [
                        "01/01/2014 12:13",
                        "31/12/2014 12:13",
                    ],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => [
                        "01/01/2014 12:13",
                        "31/12/2014 12:13",
                        "30/06/2015 12:13",
                    ],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIMESTAMP",
                    "value" => [
                        "02/03/2014 12:13",
                        "31/12/2014 12:13",
                        "30/06/2015 12:13",
                    ],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),
            // TIMES
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => "12:13:14",
                    "expected" => array()
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "flags" => IsIn::NOT,
                    "value" => "12:13:14",
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_2",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => [
                        "12:13:14",
                        "13:14:15",
                    ],
                    "expected" => array(
                        "ISIN_2"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => [
                        "12:13:14",
                        "13:14:15",
                    ],
                    "flags" => IsIn::NOT,
                    "expected" => array(
                        "ISIN_1",
                        "ISIN_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => [
                        "12:13:14",
                        "13:14:15",
                        "14:15:16",
                    ],
                    "expected" => array(
                        "ISIN_2",
                        "ISIN_3"
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TIME",
                    "value" => [
                        "16:17:18",
                        "13:14:15",
                        "14:15:16",
                    ],
                    "expected" => array(
                        "ISIN_3"
                    )
                )
            ),

        );
    }
}
