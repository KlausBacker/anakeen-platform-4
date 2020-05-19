<?php

namespace Dcp\Pu;

use Anakeen\Search\Filters\OneEqualsMulti;

class TestDcpDocumentFilterOneEqualsMulti extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ONEEQUALSMULTI';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneEqualsMulti.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneEqualsMulti
     */
    public function testOneEqualsMulti($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new OneEqualsMulti($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneEqualsMulti()
    {
        return array(
            // Empty test
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => array(),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                        "ONEEQUALSMULTI_2",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            // M_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => array("Y"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => array("Y", "A"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => OneEqualsMulti::NOT,
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => array("Y"),
                    "expected" => array(
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ENUM",
                    "flags" => OneEqualsMulti::ALL + OneEqualsMulti::NOT,
                    "value" => array("N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            // M_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(array("FOO_1")),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "value" => new LateNameResolver(array("FOO_1", "FOO_2")),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => OneEqualsMulti::NOT,
                    "value" => new LateNameResolver(array("FOO_1", "FOO_2")),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => new LateNameResolver(array("FOO_1", "FOO_2")),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_DOCID",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => new LateNameResolver(array("FOO_1")),
                    "expected" => array()
                )
            ),
            // M_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(array("U_1")),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "value" => new LateNameResolver(array("U_1", "U_3")),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => OneEqualsMulti::NOT,
                    "value" => new LateNameResolver(array("U_1", "U_3")),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => new LateNameResolver(array("U_1", "U_3")),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "M_ACCOUNT",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => new LateNameResolver(array("U_1")),
                    "expected" => array()
                )
            ),
            // A_TEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => array("Un"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => array("Un", "Deux"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "flags" => OneEqualsMulti::NOT,
                    "value" => array("Un", "Deux"),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => array("Un", "Deux"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => array("Un"),
                    "expected" => array()
                )
            ),
            // A_ENUM
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => array("Y"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => array("Y", "A"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "flags" => OneEqualsMulti::NOT,
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_1",
                        "ONEEQUALSMULTI_3",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => array("Y", "N"),
                    "expected" => array(
                        "ONEEQUALSMULTI_2",
                    )
                )
            ),
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_ENUM",
                    "flags" => OneEqualsMulti::ALL,
                    "value" => array("Y"),
                    "expected" => array(
                    )
                )
            ),
        );
    }
}
