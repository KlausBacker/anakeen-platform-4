<?php

namespace Dcp\Pu;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Internal\GlobalParametersManager;

class TestGetDocAnchor extends TestCaseDcpCommonFamily
{
    public $famName = "TST_TITLE";

    /**
     * import TST_TITLE family
     *
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_getdocanchor.ods";
    }

    /**
     * @dataProvider dataGetDocAnchorMail
     *
     * @param $data
     *
     */
    public function testGetDocAnchorMail($data)
    {
        foreach ($data['params'] as $param) {
            ContextManager::setParameterValue(\Anakeen\Core\Settings::NsSde, $param['name'], $param['value']);
        }
        GlobalParametersManager::initialize();

        $doc = SEManager::getDocument($data['doc']);
        $this->assertTrue(is_object($doc), sprintf("Could not get document with id '%s'.", $data['doc']));

        $data['expected_href'] = str_replace(['%ID%', '%INITID%'], [$doc->id, $doc->initid], $data['expected_href']);

        $anchor = $doc->getDocAnchor($doc->id, 'mail');
        $this->assertTrue(preg_match('/href=([\'"])(?P<href>.*?)\1/', $anchor, $m) === 1, sprintf("Could not find href='...' in anchor '%s'.", $anchor));
        $href = $m['href'];
        $this->assertTrue($href == $data['expected_href'], sprintf("Unexpected href '%s' (expecting '%s') in anchor '%s'.", $href, $data['expected_href'], $anchor));
    }

    public function dataGetDocAnchorMail()
    {
        return array(
            array(
                array(
                    "doc" => "TST_GETDOCANCHOR_1",
                    "params" => array(
                        array(
                            "name" => "CORE_MAILACTION",
                            "value" => ""
                        ),
                        array(
                            "name" => "CORE_URLINDEX",
                            "value" => "http://www1.example.net/"
                        )
                    ),
                    "expected_href" => "http://www1.example.net/api/v2/smart-elements/%INITID%.html"
                )
            ),
            array(
                array(
                    "doc" => "TST_GETDOCANCHOR_1",
                    "params" => array(
                        array(
                            "app" => "CORE",
                            "name" => "CORE_MAILACTION",
                            "value" => "http://www2.example.net/?app=FOO&action=BAR"
                        ),
                        array(
                            "app" => "CORE",
                            "name" => "CORE_URLINDEX",
                            "value" => ""
                        )
                    ),
                    "expected_href" => "http://www2.example.net/?app=FOO&amp;action=BAR"
                )
            )
        );
    }
}
