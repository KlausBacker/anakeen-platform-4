<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\Pu\Routes;

use Slim\Http\Environment;

require_once __DIR__ . '/../TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class CoreGetFamilyDocument extends TestCaseRoutes
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
        $import[] = __DIR__ . "/Inputs/tst_f02_1.struct.csv";
        $import[] = __DIR__ . "/Inputs/tst_f02_2.struct.csv";
        $import[] = __DIR__ . "/Inputs/docsF2.xml";
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
                'HTTP_ACCEPT' => 'application/json',
                'REQUEST_URI' => $uri
            ]
        );

        $app = $this->setApiEnv($env);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
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
                'HTTP_ACCEPT' => 'application/json',
                'QUERY_STRING' => "fields=document.properties.all"
            ]
        );

        $app = $this->setApiEnv($env);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();

        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }



    /**
     * Test Get family with attribute structure
     *
     * @dataProvider dataGetFamilyStructure
     *
     * @param string $uri
     * @param string $expectedJsonFile file path to expected result
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetFamilyStructure($uri, $expectedJsonFile)
    {
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => $uri,
                'HTTP_ACCEPT' => 'application/json',
                'QUERY_STRING' => "fields=family.structure"
            ]
        );

        $app = $this->setApiEnv($env);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();

        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }





    /**
     * Test Get family with attribute structure
     *
     * @dataProvider dataGetFamilyDocuments
     *
     * @param string $uri
     * @param string $expectedJsonFile file path to expected result
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetFamilyDocuments($uri, $expectedJsonFile)
    {
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => $uri,
                'HTTP_ACCEPT' => 'application/json',
                'QUERY_STRING' => "fields=family.structure"
            ]
        );

        $app = $this->setApiEnv($env);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();

        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }


    public function dataGetDocument()
    {
        return array(

            array(
                '/api/v2/families/TST_F2_1',
                __DIR__ . "/Expects/TST_F2_1.json"
            ),
            array(
                '/api/v2/families/TST_F2_2',
                __DIR__ . "/Expects/TST_F2_2.json"
            )
        );
    }

    public function dataGetAllPropertiesDocument()
    {
        return array(
            array(
                '/api/v2/families/TST_F2_1',
                __DIR__ . "/Expects/TST_F2_1_all.json"
            ),
            array(
                '/api/v2/families/TST_F2_2',
                __DIR__ . "/Expects/TST_F2_2_all.json"
            )
        );
    }
    public function dataGetFamilyStructure()
    {
        return array(
            array(
                '/api/v2/families/TST_F2_1',
                __DIR__ . "/Expects/TST_F2_1_struct.json"
            ),
            array(
                '/api/v2/families/TST_F2_2',
                __DIR__ . "/Expects/TST_F2_2_struct.json"
            )
        );
    }
    public function dataGetFamilyDocuments()
    {
        return array(
            array(
                '/api/v2/families/TST_F2_1/documents/',
                __DIR__ . "/Expects/TST_F2_1_docs.json"
            ),
            array(
                '/api/v2/families/TST_F2_2/documents/',
                __DIR__ . "/Expects/TST_F2_2_docs.json"
            )
        );
    }
}
