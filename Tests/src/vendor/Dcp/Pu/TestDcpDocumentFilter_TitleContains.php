<?php

namespace Dcp\Pu;


class TestDcpDocumentFilter_TitleContains extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_TITLECONTAINS';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_TitleContains.ods"
        );
    }
    /**
     * @param $test
     * @dataProvider data_TitleContains
     */
    public function test_TitleContains($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\TitleContains($test["value"], $test["flags"]) , $test["expected"]);
    }
    
    public function data_TitleContains()
    {
        return array(
            /* WORD */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "Élé",
                    "flags" => "",
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "rouge",
                    "flags" => "",
                    "expected" => array(
                        "TITLECONTAINS_2"
                    )
                )
            ) ,
            /* WORD + NOCASE */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "élé",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NOCASE,
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "rOUGE",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NOCASE,
                    "expected" => array(
                        "TITLECONTAINS_2",
                        "TITLECONTAINS_3"
                    )
                )
            ) ,
            /* WORD + NODIACRITIC */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "Ele",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC,
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "hete",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC,
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            /* WORD + NODIACRITIC + NOCASE */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "ele",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC | \Anakeen\Search\Filters\TitleContains::NOCASE,
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "HETE",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC | \Anakeen\Search\Filters\TitleContains::NOCASE,
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            /* REGEXP */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "\\ycheva(l|ux)\\y.*\\yrouge\\y",
                    "flags" => \Anakeen\Search\Filters\TitleContains::REGEXP,
                    "expected" => array(
                        "TITLECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "\\y[cm]ou",
                    "flags" => \Anakeen\Search\Filters\TitleContains::REGEXP,
                    "expected" => array(
                        "TITLECONTAINS_3",
                        "TITLECONTAINS_5"
                    )
                )
            ) ,
            /* REGEXP + NOCASE */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "\\ycheva(l|ux)\\y.*\\yrouge\\y",
                    "flags" => \Anakeen\Search\Filters\TitleContains::REGEXP | \Anakeen\Search\Filters\TitleContains::NOCASE,
                    "expected" => array(
                        "TITLECONTAINS_2",
                        "TITLECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "\\y[rm]ou",
                    "flags" => \Anakeen\Search\Filters\TitleContains::REGEXP | \Anakeen\Search\Filters\TitleContains::NOCASE,
                    "expected" => array(
                        "TITLECONTAINS_2",
                        "TITLECONTAINS_3",
                        "TITLECONTAINS_5"
                    )
                )
            ) ,
            /* WORD + NODIACRITIC */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "elephant",
                    "flags" => \Anakeen\Search\Filters\TitleContains::NODIACRITIC,
                    "expected" => array(
                        "TITLECONTAINS_4"
                    )
                )
            ) ,
            /* REGEXP + NODIACRITIC */
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "\\y[PH]É[LT]",
                    "flags" => \Anakeen\Search\Filters\TitleContains::REGEXP | \Anakeen\Search\Filters\TitleContains::NODIACRITIC,
                    "expected" => array(
                        "TITLECONTAINS_2",
                        "TITLECONTAINS_4"
                    )
                )
            )
        );
    }
}
