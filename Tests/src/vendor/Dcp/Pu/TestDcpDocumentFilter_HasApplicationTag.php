<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;


use Anakeen\Core\SEManager;

class TestDcpDocumentFilter_HasApplicationTag extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_HASAPPLICATIONTAG';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_HasApplicationTag.ods"
        );
    }
    protected function setUp()
    {
        parent::setUp();
        $this->localSetup();
    }
    public function localSetup()
    {
        $doc = SEManager::getDocument('HASAPPLICATIONTAG_2');
        $doc->addATag('foo');
        unset($doc);
        $doc = SEManager::getDocument('HASAPPLICATIONTAG_3');
        $doc->addATag('FOO');
        unset($doc);
    }
    /**
     * @param $test
     * @dataProvider data_HasApplicationTag
     */
    public function test_HasApplicationTag($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\HasApplicationTag($test["value"]) , $test["expected"]);
    }
    
    public function data_HasApplicationTag()
    {
        return array(
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "foo",
                    "expected" => array(
                        "HASAPPLICATIONTAG_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "FOO",
                    "expected" => array(
                        "HASAPPLICATIONTAG_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "value" => "bar",
                    "expected" => array()
                )
            )
        );
    }
}
