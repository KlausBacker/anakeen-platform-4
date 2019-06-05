<?php

namespace Anakeen\Pu;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Ui\ExportRenderAllConfiguration;

class TestExportSmartConfiguration extends \Dcp\Pu\TestCaseDcpCommonFamily
{
    const XSD_PATH = "vendor/Anakeen/XmlSchemas/smart/1.0/config.xsd";

    /**
     * @dataProvider dataValidationXsd
     *
     * @param $structureName
     *
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function testValidationXsd($structureName)
    {
        $structure = SEManager::getFamily($structureName);
        $this->assertNotEmpty($structure, sprintf("Structure \"%s\" does not exists", $structureName));
        $export = new ExportRenderAllConfiguration($structure);
        $export->extract();
        $xml = $export->toXml();
        $this->assertNotEmpty($xml, sprintf("Xml export from structure \"%s\" is empty", $structureName));

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($xml), sprintf("export from structure \"%s\" is not an XML file", $structureName));
        $xsdPath = realpath(sprintf("%s/%s", ContextManager::getRootDirectory(), self::XSD_PATH));
        $this->assertFileExists($xsdPath);

        $xmlValidated = @$dom->schemaValidate($xsdPath);
        $xmlErrors = libxml_get_errors();

        $this->assertTrue($xmlValidated, sprintf("%s\n\nXml file does not comply with XSD file \"%s\"\n%s", $xml, $xsdPath, $this->getXmlErrors($xmlErrors)));
    }

    private function getXmlErrors(array $errors)
    {
        $errorResult = array();
        foreach ($errors as $error) {
            /**@var \LibXMLError $error */
            $errorResult[] = sprintf("Error found at line %s with message :\n%s", $error->line, trim($error->message));
        }
        return implode("\n", $errorResult);
    }

    public function dataValidationXsd()
    {
        return array(
            array(
                "structure" => "TST_DDUI_DOCID",
            ),
            array(
                "structure" => "TST_DDUI_ALLTYPE",
            ),
            array(
                "structure" => "TST_DDUI_COLOR",
            ),
            array(
                "structure" => "TST_DDUI_ENUM",
            ),
            array(
                "structure" => "IGROUP",
            ),
            array(
                "structure" => "IUSER",
            )
        );
    }
}
