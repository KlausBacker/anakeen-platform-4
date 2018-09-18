<?php

namespace Dcp\Pu;


use Anakeen\Core\SEManager;

class TestDcpDocumentFilter_IdentifiersIn extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_IDENTIFIERSIN';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_IdentifiersIn.ods"
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
        $doc = SEManager::getDocument('IDENTIFIERSIN_2');
        $doc->revise();
        $staticEntries['IDENTIFIERSIN_2_BIS'] = $doc->id;
        unset($doc);
        $doc = SEManager::getDocument('IDENTIFIERSIN_3');
        $doc->revise();
        $staticEntries['IDENTIFIERSIN_3_BIS'] = $doc->id;
        unset($doc);
        \Dcp\Pu\LateNameResolver::setStaticEntries($staticEntries);
    }
    /**
     * @param $test
     * @dataProvider data_IdentifiersIn
     */
    public function test_IdentifiersIn($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\IdentifiersIn($test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_IdentifiersIn()
    {
        return array(
            // ID
            array(
                array(
                    "fam" => self::FAM,
                    "value" => new \Dcp\Pu\LateNameResolver("IDENTIFIERSIN_1") ,
                    "expected" => array(
                        "IDENTIFIERSIN_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => new \Dcp\Pu\LateNameResolver(array(
                        "IDENTIFIERSIN_2_BIS",
                        "IDENTIFIERSIN_3_BIS"
                    )) ,
                    "expected" => array(
                        "IDENTIFIERSIN_2",
                        "IDENTIFIERSIN_3"
                    )
                )
            ) ,
            // INITID
            array(
                array(
                    "fam" => self::FAM,
                    "value" => new \Dcp\Pu\LateNameResolver(array(
                        "IDENTIFIERSIN_1",
                        "IDENTIFIERSIN_2_BIS"
                    )) ,
                    "flags" => \Anakeen\Search\Filters\IdentifiersIn::INITID,
                    "expected" => array(
                        "IDENTIFIERSIN_1"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => new \Dcp\Pu\LateNameResolver(array(
                        "IDENTIFIERSIN_2_BIS",
                        "IDENTIFIERSIN_3_BIS"
                    )) ,
                    "flags" => \Anakeen\Search\Filters\IdentifiersIn::INITID,
                    "expected" => array()
                )
            )
        );
    }
}
