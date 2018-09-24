<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;


class TestDcpDocumentFilter_DocumentTitle extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_DOCUMENTTITLE';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_DocumentTitle.ods"
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
     * @dataProvider data_DocumentTitle
     */
    public function test_DocumentTitle($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\DocumentTitle($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_DocumentTitle()
    {
        return array(
            // S_DOCID
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_DOCID",
                    "value" => "Foo Un",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_DOCID",
                    "value" => "Foo Un",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::NOCASE,
                    "expected" => array(
                        "DOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_DOCID",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_DOCID",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\DocumentTitle::NOCASE,
                    "expected" => array(
                        "DOCUMENTTITLE_2"
                    )
                )
            ) ,
            // S_ACCOUNT
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_ACCOUNT",
                    "value" => "Un Uh",
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_ACCOUNT",
                    "value" => "Un Uh",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::NOCASE,
                    "expected" => array(
                        "DOCUMENTTITLE_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::MATCH_REGEXP,
                    "expected" => array()
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_ACCOUNT",
                    "value" => "\\yUn\\y",
                    "flags" => \Anakeen\Search\Filters\DocumentTitle::MATCH_REGEXP | \Anakeen\Search\Filters\DocumentTitle::NOCASE,
                    "expected" => array(
                        "DOCUMENTTITLE_2"
                    )
                )
            )
        );
    }
}
