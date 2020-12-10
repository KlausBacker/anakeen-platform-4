<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\TestUnits\CoreTests;

/**
 * @author Anakeen
 * @package Dcp\Pu
 */

//require_once 'PU_testcase_dcp_document.php';

class TestOooLayout extends TestCaseDcpDocument
{
    protected static $outputDir;

    protected function tearDown(): void
    {
    }

    protected function setUp(): void
    {
        $this->logTest();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // self::$outputDir = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/oootest-");
        self::$outputDir = (\Anakeen\Core\ContextManager::getTmpDir() . "/oootest");
        if (!is_dir(self::$outputDir)) {
            mkdir(self::$outputDir);
        }
        self::connectUser();
        self::beginTransaction();

        self::importDocument("PU_data_dcp_oooLayout.ods");
    }

    public static function tearDownAfterClass(): void
    {
        self::rollbackTransaction();
        CoreTests::addMessage(sprintf("Results for %s in file://%s", __CLASS__, self::$outputDir));
    }

    protected function saveFileResult($file, $name)
    {
        copy($file, self::$outputDir . '/' . $name);
    }

    /**
     * @dataProvider dataErrorXML
     * @param string $layoutFile
     * @param array $data
     * @return void
     */
    public function testErrorXML($layoutFile, array $data, $expectedCode)
    {
        $l = new \Anakeen\Layout\OooLayout(sprintf("%s/Apps/DCPTEST/Layout/%s", DEFAULT_PUBDIR, $layoutFile));
        foreach ($data as $k => $v) {
            $l->set($k, $v);
        }
        try {
            $f = $l->gen();
            $this->fail(sprintf("Generate %s", $f));
        } catch (\Anakeen\Layout\Exception $e) {
            $this->assertEquals($expectedCode, $e->getDcpCode(), "not correct code :" . $e->getMessage());
            $this->assertNotEmpty($e->getCorruptedFile());
        }
    }

    /**
     * @dataProvider dataSimpleLayout
     * @param string $instances
     * @param string $lname
     * @param array $templates
     */
    public function testSimpleLayout($instances, $lname, array $templates)
    {
        $df = new_doc(self::$dbaccess, "TST_OOOLAYOUT");
        $this->assertTrue($df->isAlive(), "family TST_OOOLAYOUT is not alive");
        $this->importDocument($instances);

        $instance = new_doc(self::$dbaccess, $lname);
        $this->assertTrue($instance->isAlive(), "document $lname is not alive");

        foreach ($templates as $template) {
            $file = '';
            try {
                $file = $instance->viewDoc("DCPTEST:" . $template . ":B");
            } catch (\Anakeen\Layout\Exception $e) {
                $this->fail(sprintf("%s, \n file: %s", $e->getMessage(), $e->getCorruptedFile()));
            }
            $this->assertTrue(file_exists($file), "fail layout $template : $file");
            // print "ooffice $file\n";
            $this->saveFileResult($file, sprintf("%s_%s.odt", str_replace('.odt', '', $template), $lname));
        }
        //print_r($instance->getValues());
    }

    /**
     * @dataProvider dataErrorHtmlLayout
     * @param string $instances
     * @param string $lname
     * @param array $templates
     * @param string $expectedCode
     * @return void
     */
    public function testErrorHtmlLayout($instances, $lname, array $templates, $expectedCode)
    {
        $df = new_doc(self::$dbaccess, "TST_OOOLAYOUT");
        $this->assertTrue($df->isAlive(), "family TST_OOOLAYOUT is not alive");
        $this->importDocument($instances);

        $instance = new_doc(self::$dbaccess, $lname);
        $this->assertTrue($instance->isAlive(), "document $lname is not alive");
        foreach ($templates as $template) {
            try {
                $instance->viewDoc("DCPTEST:" . $template . ":B");
                $this->fail(sprintf("No error detected. Need find %s code", $expectedCode));
            } catch (\Anakeen\Layout\exception $e) {
                $this->assertEquals($expectedCode, $e->getDcpCode(), sprintf("%s, \n corrupted file is : %s", $e->getMessage(), $e->getCorruptedFile()));
                $this->assertNotEmpty($e->getCorruptedFile(), "no corrupted file found");
            }
        }
    }

    /**
     * @dataProvider dataGoodXML
     * @param string $layoutFile
     * @param array $data
     * @return void
     */
    public function testGoodXML($layoutFile, array $data)
    {
        $l = new \Anakeen\Layout\OooLayout(sprintf("%s/Apps/DCPTEST/Layout/%s", DEFAULT_PUBDIR, $layoutFile));
        foreach ($data as $k => $v) {
            $l->set($k, $v);
        }
        $f = $l->gen();
        $this->assertNotEmpty($f, "file is not produced");
    }

    /**
     * @dataProvider dataInvalidXMLChars
     */
    public function testInvalidXMLChars($data)
    {
        $err = '';
        $doc = createTmpDoc(self::$dbaccess, $data['family']);
        foreach ($data['setValue'] as $k => $v) {
            $err .= $doc->setValue($k, $v);
        }
        $out = '';
        $exceptionMessage = '';
        try {
            $out = $doc->viewDoc("DCPTEST:" . $data['template'] . ":B");
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }
        $this->assertTrue(is_file($out), sprintf("Unexpected error from viewDoc(): %s", $out));
        $this->assertEmpty($exceptionMessage, sprintf("Unexpected exception from viewDoc(): %s", $exceptionMessage));
    }

    public function dataErrorXML()
    {
        return array(
            array(
                "PU_dcp_data_customOooLayout.odt",
                array(
                    "X" => "Tree & Two",
                    "Y" => "Two",
                    "Z" => "One"
                ),
                "LAY0004"
            ),
            array(
                "PU_dcp_data_customOooLayout.odt",
                array(
                    "X" => "Tree  <Two>",
                    "Y" => "Two",
                    "Z" => "One"
                ),
                "LAY0004"
            ),
            array(
                "PU_dcp_data_customOooLayout.odt",
                array(
                    "X" => "Tree 'test &zou;",
                    "Y" => "Two",
                    "Z" => "One"
                ),
                "LAY0004"
            ),
            array(
                "PU_dcp_data_customOooLayout_no_automatic-styles.odt",
                array(),
                "LAY0009"
            )
        );
    }

    public function dataGoodXML()
    {
        return array(
            array(
                "PU_dcp_data_customOooLayout.odt",
                array(
                    "X" => "Tree",
                    "Y" => "Two",
                    "Z" => "One"
                )
            ),
            array(
                "PU_dcp_data_customOooLayout.odt",
                array(
                    "X" => "Tree <text:line-break/>",
                    "Y" => "<text:span>Two</text:span>",
                    "Z" => '<text:span text:style-name="Tbold">One</text:span>"'
                )
            ),
            array(
                "PU_dcp_data_customOooLayout.odt",
                array(
                    "X" => "Tree &amp; Two",
                    "Y" => "&lt;One&gt;"
                )
            )
        );
    }

    public function dataErrorHtmlLayout()
    {
        return array(
            array(
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_SIMPLEODT",
                array(
                    "PU_dcp_data_errorInlineOooLayout.odt"
                ),
                "LAY0005"
            ),

            array(
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_SIMPLEODT",
                array(
                    "PU_dcp_data_errorPuceHtmlOooLayout.odt"
                ),
                "LAY0002"
            )
        );
    }

    public function dataSimpleLayout()
    {
        return array(
            array(
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_SIMPLEODT",
                array(
                    "PU_dcp_data_simpleOooLayout.odt",
                    "PU_dcp_data_repeatMulti.odt",
                    "PU_dcp_data_repeatOne.odt",
                    "PU_dcp_data_repeatOooLayout.odt",
                    "PU_dcp_data_rowOooLayout.odt",
                    "PU_dcp_data_ifOooLayout.odt"
                )
            ),
            array(
                "PU_dcp_data_multipleOooLayout.xml",
                "TST_MULTIPLEODT",
                array(
                    "PU_dcp_data_multipleOooLayout.odt"
                )
            ),
            array(
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_TABLEODT",
                array(
                    "PU_dcp_data_simpleOooLayout.odt",
                    "PU_dcp_data_puceOooLayout.odt"
                )
            ),
            array(
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_PUCEODT",
                array(
                    "PU_dcp_data_simpleOooLayout.odt",
                    "PU_dcp_data_puceOooLayout.odt"
                )
            )
        );
    }

    public function dataInvalidXMLChars()
    {
        return array(
            /* PCDATA invalid Char value 1 in Entity */
            array(
                array(
                    "family" => "TST_OOOLAYOUT",
                    "setValue" => array(
                        "TST_TITLE" => "B(SOH: \x01)"
                    ),
                    "template" => "PU_dcp_data_simple1.odt"
                )
            ),
            array(
                array(
                    "family" => "TST_OOOLAYOUT",
                    "setValue" => array(
                        "HTML_MULTI" => ["A(SOH: \x01)"]
                    ),
                    "template" => "PU_dcp_data_simple1.odt"
                )
            ),
            /* Char 0xFFFE out of allowed range in Entity + PCDATA invalid Char value 65534 in Entity */
            array(
                array(
                    "family" => "TST_OOOLAYOUT",
                    "setValue" => array(
                        "TST_TITLE" => "E(\xef\xbf\xbe)"
                    ),
                    "template" => "PU_dcp_data_simple1.odt"
                )
            ),
            array(
                array(
                    "family" => "TST_OOOLAYOUT",
                    "setValue" => array(
                        "HTML_MULTI" => ["D(\xef\xbf\xbe)"]
                    ),
                    "template" => "PU_dcp_data_simple1.odt"
                )
            )
        );
    }
}
