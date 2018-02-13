<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\Pu\Routes;

use Slim\Http\Environment;

require_once __DIR__ . '/../TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class CorePutDocument extends TestCaseRoutes
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
        $import[] = __DIR__ . "/Inputs/TST_API_ALLTYPE__STRUCT.csv";
        $import[] = __DIR__ . "/Inputs/doc1.xml";
        $import[] = __DIR__ . "/Inputs/doc2.xml";
        return $import;
    }

    protected $app;

    /**
     * Test Simple Get Document
     *
     * @dataProvider dataPutDocument
     *
     * @param $uri
     * @param string $postContent json data
     * @param $expectedJsonFile
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testPutDocument($uri, $postContent, $expectedJsonFile)
    {
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'PUT',
                'REQUEST_URI' => $uri,
                'CONTENT_TYPE' => 'application/json;charset=utf8',
                'CONTENT' => $postContent
            ]
        );

        $app = $this->setApiEnv($env);
        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }

    public function dataPutDocument()
    {
        return array(

            array(
                '/api/v2/documents/TST_APIDOC01',
                json_encode([
                    "document" => [
                        "attributes" => [
                            "tst_api__title" => [
                                "value" => "Hello World"
                            ]
                        ]
                    ]
                ]),

                __DIR__."/Expects/doc1Hello.json"
            )
        );
    }
}
