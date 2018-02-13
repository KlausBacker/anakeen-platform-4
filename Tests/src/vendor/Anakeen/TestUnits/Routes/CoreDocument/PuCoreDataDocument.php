<?php

namespace Anakeen\Pu\Routes;

require_once __DIR__.'/../TestCaseRoutes.php';
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

        $app=$this->setApiUriEnv($uri);

        $response = $app->run(true);
        $rawBody=(string)$response->getBody();
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


    public function dataGetDocument()
    {
        return array(
            array(
                'GET /api/v2/documents/1',
                __DIR__."/Expects/base.json"
            ),
            array(
                'GET /api/v2/documents/2',
                __DIR__."/Expects/dir.json"
            ),
            array(
                'GET /api/v2/documents/TST_APIDOC01',
                __DIR__."/Expects/doc1.json"
            ),
            array(
                'GET /api/v2/documents/1?fields=document.properties.all',
                __DIR__."/Expects/baseAllProperties.json"
            ),
            array(
                'GET /api/v2/documents/2?fields=document.properties.all',
                __DIR__."/Expects/dirAllProperties.json"
            )
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
}
