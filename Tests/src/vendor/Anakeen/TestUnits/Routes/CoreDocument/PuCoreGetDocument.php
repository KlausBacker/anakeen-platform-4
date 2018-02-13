<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\Pu\Routes;

use Slim\Http\Environment;

require_once __DIR__.'/../TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class CoreGetDocument extends TestCaseRoutes
{

    protected static $importCsvEnclosure = '"';
    protected static $importCsvSeparator = ";";
    /**
     * import TST_APIBASE family
     *
     * @static
     * @return string|string[]
     */
    protected static function getCommonImportFile()
    {
        $import = array();
        $import[] = __DIR__."/Inputs/tst_f01.struct.csv";
        $import[] = __DIR__."/Inputs/doc1.xml";
        return $import;
    }

    protected $app;

    /**
     * Test Simple Get Document
     *
     * @dataProvider dataGetDocument
     *
     * @param $uri
     * @param $expectedJsonFile
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetDocument($uri, $expectedJsonFile)
    {
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => $uri
            ]
        );

        $app=$this->setApiEnv($env);

        $response = $app->run(true);
        $rawBody=(string)$response->getBody();
        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }


    /**
     * Test Get Document with all properties
     *
     * @dataProvider dataGetAllPropertiesDocument
     *
     * @param string $uri
     * @param string $expectedJsonFile file path to expected result
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetAllPropertiesDocument($uri, $expectedJsonFile)
    {
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => $uri,
                'QUERY_STRING' => "fields=document.properties.all"
            ]
        );

        $app=$this->setApiEnv($env);

        $response = $app->run(true);
        $rawBody=(string)$response->getBody();

        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }

    public function dataGetDocument()
    {
        return array(
            array(
                '/api/v2/documents/1',
                __DIR__."/Expects/base.json"
            ),
            array(
                '/api/v2/documents/2',
                __DIR__."/Expects/dir.json"
            ),
            array(
                '/api/v2/documents/TST_APIDOC01',
                __DIR__."/Expects/doc1.json"
            )
        );
    }
    public function dataGetAllPropertiesDocument()
    {
        return array(
            array(
                '/api/v2/documents/1',
                __DIR__."/Expects/baseAllProperties.json"
            ),
            array(
                '/api/v2/documents/2',
                __DIR__."/Expects/dirAllProperties.json"
            )
        );
    }
}
