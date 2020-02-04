<?php

namespace Dcp\Pu;

class TestDcpDocumentFilterOneContains extends TestDcpDocumentFiltercommon
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_ONECONTAINS';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_OneContains.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider dataOneContains
     */
    public function testOneContains($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\OneContains($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataOneContains()
    {
        return array(
            // A_TEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "n",
                    "expected" => array(
                        "ONECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "N",
                    "flags" => \Anakeen\Search\Filters\OneContains::NOCASE,
                    "expected" => array(
                        "ONECONTAINS_2"
                    )
                )
            ) ,
            // A_HTMLTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "n",
                    "expected" => array(
                        "ONECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "N",
                    "flags" => \Anakeen\Search\Filters\OneContains::NOCASE,
                    "expected" => array(
                        "ONECONTAINS_2"
                    )
                )
            ) ,
            // A_LONGTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "n",
                    "expected" => array(
                        "ONECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "N",
                    "flags" => \Anakeen\Search\Filters\OneContains::NOCASE,
                    "expected" => array(
                        "ONECONTAINS_2"
                    )
                )
            ) ,
        );
    }
    /**
     * @param $test
     * @dataProvider dataNoOneContains
     */
    public function testNoOneContains($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\NoOneContains($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)), $test["expected"]);
    }
    
    public function dataNoOneContains()
    {
        return array(
            // A_TEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "n",
                    "expected" => array(
                        "ONECONTAINS_1",
                        "ONECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_TEXT",
                    "value" => "N",
                    "flags" => \Anakeen\Search\Filters\OneContains::NOCASE,
                    "expected" => array(
                        "ONECONTAINS_1",
                        "ONECONTAINS_3"
                    )
                )
            ) ,
            // A_HTMLTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "n",
                    "expected" => array(
                        "ONECONTAINS_1",
                        "ONECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "N",
                    "flags" => \Anakeen\Search\Filters\OneContains::NOCASE,
                    "expected" => array(
                        "ONECONTAINS_1",
                        "ONECONTAINS_3"
                    )
                )
            ) ,
            // A_LONGTEXT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_LONGTEXT",
                    "value" => "n",
                    "expected" => array(
                        "ONECONTAINS_1",
                        "ONECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_HTMLTEXT",
                    "value" => "N",
                    "flags" => \Anakeen\Search\Filters\OneContains::NOCASE,
                    "expected" => array(
                        "ONECONTAINS_1",
                        "ONECONTAINS_3"
                    )
                )
            )
        );
    }
}
