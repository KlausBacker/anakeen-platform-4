<?php

namespace Anakeen\Pu\Routes;

use Dcp\Core\DocManager;

require_once __DIR__ . '/../TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class CoreDataDocument extends TestCaseRoutes
{
    /**
     * import TST_APIBASE family
     *
     * @static
     * @return string|string[]
     */
    protected static function getCommonImportFile()
    {
        $import = array();
        $import[] = __DIR__ . "/Inputs/tst_f01.struct.csv";
        $import[] = __DIR__ . "/Inputs/doc1.xml";
        $import[] = __DIR__ . "/Inputs/doc2.xml";
        $import[] = __DIR__ . "/Inputs/docs.xml";
        return $import;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();


        $doc = DocManager::getDocument("TST_APIDOC13");

        $title = $doc->getTitle();

        for ($i = 0; $i < 3; $i++) {
            $doc->setValue("tst_api__title", sprintf("%s - #%02d", $title, $doc->revision + 1));
            $doc->revise(sprintf("Test revision #%d", $doc->revision + 1));
        }


        $doc = DocManager::getDocument("TST_APIDOC14_DEL");
        $doc->delete();
        $doc = DocManager::getDocument("TST_APIDOC15_DEL");
        $doc->revise();
        $doc->delete();
        $doc = DocManager::getDocument("TST_APIDOC16_DEL");
        $doc->delete();
    }

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

        $app = $this->setApiUriEnv($uri);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }

    /**
     * Test Simple Put (modify) Document
     *
     * @dataProvider dataPutDocument
     *
     * @param        $uri
     * @param string $postContent json data
     * @param        $expectedJsonFile
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testPutDocument($uri, $postContent, $expectedJsonFile)
    {
        $app = $this->setApiUriEnv($uri, $postContent);
        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }

    /**
     * Test Restore deleted Document
     *
     * @dataProvider dataTrash
     *
     * @param        $uri
     * @param string $postContent json data
     * @param        $expectedJsonFile
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testTrash($uri, $postContent, $expectedJsonFile)
    {
        $this->testPutDocument($uri, $postContent, $expectedJsonFile);
    }

    public function dataGetDocument()
    {
        return array(
            array(
                'GET /api/v2/documents/1',
                __DIR__ . "/Expects/base.json"
            ),
            array(
                'GET /api/v2/documents/2',
                __DIR__ . "/Expects/dir.json"
            ),
            array(
                'GET /api/v2/documents/TST_APIDOC01',
                __DIR__ . "/Expects/doc1.json"
            ),
            array(
                'GET /api/v2/documents/1?fields=document.properties.all',
                __DIR__ . "/Expects/baseAllProperties.json"
            ),
            array(
                'GET /api/v2/documents/2?fields=document.properties.all',
                __DIR__ . "/Expects/dirAllProperties.json"
            ),

            array(
                'GET /api/v2/documents/TST_APIDOC13',
                __DIR__ . "/Expects/doc13.json"
            ),
            array(
                'GET /api/v2/documents/TST_APIDOC13/revisions/0',
                __DIR__ . "/Expects/doc13_rev0.json"
            ),
            array(
                'GET /api/v2/documents/TST_APIDOC13/revisions/1',
                __DIR__ . "/Expects/doc13_rev1.json"
            ),
            array(
                'GET /api/v2/documents/TST_APIDOC13/revisions/3',
                __DIR__ . "/Expects/doc13.json"
            ),
            array(
                'GET /api/v2/trash/TST_APIDOC14_DEL',
                __DIR__ . "/Expects/doc14.json"
            ),
            array(
                'GET /api/v2/trash/TST_APIDOC15_DEL',
                __DIR__ . "/Expects/doc15.json"
            ),
        );
    }


    public function dataPutDocument()
    {
        return array(

            array(
                'PUT /api/v2/documents/TST_APIDOC01',
                json_encode([
                    "document" => [
                        "attributes" => [
                            "tst_api__title" => [
                                "value" => "Hello World"
                            ]
                        ]
                    ]
                ]),

                __DIR__ . "/Expects/doc1Hello.json"
            )
        );
    }


    public function dataTrash()
    {
        return array(

            array(
                'PUT /api/v2/trash/TST_APIDOC16_DEL',
                json_encode([
                    "document" => [
                        "properties" => [
                            "status" => "alive"

                        ]
                    ]
                ]),

                __DIR__ . "/Expects/doc16.json"
            ),
            array(
                'DELETE /api/v2/documents/TST_APIDOC17',
                null,
                __DIR__ . "/Expects/doc17.json"
            )
        );
    }
}
