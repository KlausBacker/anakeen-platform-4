<?php

namespace Dcp\Pu;

use Anakeen\Core\ContextManager;
use Anakeen\Exchange\ExportXmlFolder;
use Anakeen\Exchange\ImportTar;

class TestExportXml extends TestCaseDcpCommonFamily
{
    /**
     * @var \DOMDocument
     */
    private $dom;

    public static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportfamily.ods",
            "PU_data_dcp_exportrelation.ods",
            "PU_data_dcp_exporttitlelimits.ods",
            "PU_data_dcp_exportdocimagexml.ods"
        );
    }

    /**
     * @dataProvider dataDocumentFiles
     */
    public function testExportRelation($docName, $attrName, $expectedValue)
    {

        $dom = $this->getExportedSearchDom();
        $this->domTestExportValue($dom, $docName, $attrName, 'name', $expectedValue);
    }

    /**
     * @return \DOMDocument
     */
    private function getExportedSearchDom()
    {
        if (!$this->dom) {
            $s = new \Anakeen\Search\Internal\SearchSmartData(self::$dbaccess, "TST_EXPORTFAM1");
            //print_r( $s->search());
            $export = new ExportXmlFolder();

            $export->setOutputFormat(ExportXmlFolder::xmlFormat);
            $export->useIdentificator(false);
            $export->exportFromSearch($s);

            $this->dom = new \DOMDocument();
            $this->dom->load($export->getOutputFile());
            @unlink($export->getOutputFile());
            return $this->dom;
        } else {
            return $this->dom;
        }
    }

    private function domTestExportValue(\DOMDocument $dom, $docName, $attrName, $domAttr, $expectedValue)
    {

        $docs = $dom->getElementsByTagName("tst_exportfam1");
        /**
         * \DOMElement $xmldoc
         */
        $xmldoc = null;
        foreach ($docs as $doc) {
            /**
             * @var \DOMElement $doc
             */

            if ($doc->getAttribute('name') == $docName) {
                $xmldoc = $doc;
                break;
            }
        }

        $this->assertNotEmpty($xmldoc, sprintf("document %s not found in xml", $docName));
        /**
         * @var \DOMNodeList $attrs
         */
        $attrs = $xmldoc->getElementsByTagName($attrName);
        if (!is_array($expectedValue)) {
            $expectedValue = array(
                $expectedValue
            );
        }
        $this->assertEquals(count($expectedValue), $attrs->length, sprintf("attribute %s not found in %s document", $attrName, $docName));

        $ka = 0;
        foreach ($attrs as $attr) {
            /**
             * @var \DOMElement $attr
             */
            if ($domAttr == 'content') {
                $value = $attr->nodeValue;
            } else {
                $value = $attr->getAttribute($domAttr);
            }
            $this->assertTrue(
                $expectedValue[$ka] === $value,
                sprintf("incorrect value for attribute %s in %s document ([%s] !== [%s])", $attrName, $docName, $expectedValue[$ka], $value)
            );
            $ka++;
        }
    }


    /**
     * @dataProvider dataValues
     */
    public function testExportSimpleValue($docName, $attrName, $expectedValue)
    {

        $dom = $this->getExportedSearchDom();
        $this->domTestExportValue($dom, $docName, $attrName, 'content', $expectedValue);
    }

    /**
     * @dataProvider dataExportTitleLimits
     */
    public function testExportTitleLimits($folderId)
    {
        if (file_exists("/.dockerenv")) {
            //$this->markTestSkipped(sprintf("Skipping test because Docker's AUFS has a NAME_LEN < 255 (see #2399 and #6336)"));
            $this->assertTrue(true);
            return;
        }
        $export = new \Anakeen\Exchange\ExportXmlFolder();
        $catchedMessage = '';
        try {
            $export->setOutputFormat(ExportXmlFolder::xmlFormat);
            $export->useIdentificator(false);
            $export->exportFromFolder($folderId);
            $this->dom = new \DOMDocument();
            $this->dom->load($export->getOutputFile());
            @unlink($export->getOutputFile());
        } catch (\Exception $e) {
            $catchedMessage = $e->getMessage();
        }
        $this->assertEmpty($catchedMessage, sprintf("Export thrown an unexpected exception: %s", $catchedMessage));
        $this->assertTrue(
            (is_object($this->dom) && isset($this->dom->documentElement) && $this->dom->documentElement !== null),
            sprintf(
                "Invalid XML export for folder '%s': %s",
                $folderId,
                ($catchedMessage != '') ? $catchedMessage : '<no-error-message>'
            )
        );
    }

    /**
     * Test that exported documents have no param columns
     *
     * @param string $archiveFile
     * @param        $needles
     * @param        $type
     *
     * @throws \Anakeen\Exception
     * @dataProvider dataExportImage
     */
    public function testExportImageXmlZip($archiveFile, $needles, $type)
    {
        include_once("WHAT/Lib.Http.php");
        include_once('Lib.FileDir.php');

        $this->clearSetHttpVar();
        $oImport = new \Anakeen\Exchange\ImportDocument();
        $oImport->importDocuments($archiveFile, false, true);
        $err = $oImport->getErrorMessage();
        if ($err) {
            throw new \Anakeen\Exception($err);
        }

        $folderId = "TEXT_FOLDER_EXPORT_IMAGE_XML";
        $famid = "TST_EXPORT_IMAGE_XML";
        $testFolder = uniqid(ContextManager::getTmpDir() . "/testexportimage");
        $testExtractFolder = uniqid(ContextManager::getTmpDir() . "/testexportextractimage");
        mkdir($testFolder);
        $testarchivefile = $testFolder . "/xml";
        if ($type == "X") {
            $testarchivefile .= ".zip";
        } else {
            $testarchivefile .= ".xml";
        }
        SetHttpVar("wfile", "Y");

        \Anakeen\Exchange\ExportXml::exportxmlfld($folderId, $famid, null, $testarchivefile, $type, "Y", false);

        if ($type == "X") {
            $err = ImportTar::extractTar($testarchivefile, $testExtractFolder);
            $this->assertEmpty($err, sprintf("Unexpected error while extracting archive '%s': %s", $testarchivefile, $err));
        } else {
            $testExtractFolder = $testFolder;
        }

        $output = array();
        exec(sprintf("cat %s/*.xml", escapeshellarg($testExtractFolder)), $output);
        foreach ($needles as $needle) {
            $found = false;
            foreach ($output as $line) {
                if (stripos($line, $needle) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, sprintf("file %s not found in export archive", $needle));
        }
        remove_dir($testFolder);
    }

    public function dataExportImage()
    {
        return array(
            array(
                self::$testDataDirectory . DIRECTORY_SEPARATOR . "PU_dcp_data_exportxmlimage.zip",
                array(
                    "PU_data_dcp_exportdocimageexample.png",
                    "PU_data_dcp_exportdocimage.ods"
                ),
                "X"
            ),
            array(
                self::$testDataDirectory . DIRECTORY_SEPARATOR . "PU_dcp_data_exportxmlimage.zip",
                array(
                    "PU_data_dcp_exportdocimageexample.png",
                    "PU_data_dcp_exportdocimage.ods"
                ),
                "Y"
            )
        );
    }

    public function dataDocumentFiles()
    {
        return array(
            array(
                "TST_REL1",
                "tst_relone",
                "TST_OUTREL1"
            ),
            array(
                "TST_REL1",
                "tst_account",
                "TST_U_USERONE"
            ),
            array(
                "TST_REL2",
                "tst_relmul",
                "TST_OUTREL1,TST_OUTREL2,TST_OUTREL3"
            ),
            array(
                "TST_REL3",
                "tst_colrelone",
                array(
                    "TST_OUTREL1",
                    "TST_OUTREL2",
                    "TST_OUTREL3"
                )
            ),
            array(
                "TST_REL4",
                "tst_colrelmul",
                array(
                    "TST_OUTREL1",
                    "TST_OUTREL1,TST_OUTREL3",
                    "TST_OUTREL1,TST_OUTREL2,TST_OUTREL3"
                )
            )
        );
    }

    public function dataValues()
    {
        return array(
            array(
                "TST_NUM1",
                "tst_number",
                "1"
            ),
            array(
                "TST_NUM0",
                "tst_number",
                "0"
            ),
            array(
                "TST_DATE1",
                "tst_date",
                "2012-02-20"
            ),
            array(
                "TST_DATE1",
                "tst_number",
                ""
            )
        );
    }

    public function dataExportTitleLimits()
    {
        return array(
            array(
                "TST_EXPORTTITLELIMITS_DIR"
            )
        );
    }
}
