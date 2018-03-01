<?php

namespace Anakeen\Pu\Routes;

use Dcp\Core\DocManager;

require_once __DIR__ . '/../TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class CoreDataFamilyDocument extends TestCaseRoutes
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
        $import[] = __DIR__ . "/Inputs/tst_f02_1.struct.csv";
        $import[] = __DIR__ . "/Inputs/tst_f02_2.struct.csv";
        $import[] = __DIR__ . "/Inputs/docsF2.xml";
        return $import;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();


        $doc = DocManager::getDocument("TST_F21D1");

        $title = $doc->getTitle();

        for ($i = 0; $i < 3; $i++) {
            $doc->setValue("tst_f2_title", sprintf("%s - #%02d", $title, $doc->revision + 1));
            $doc->revise(sprintf("Test revision #%d", $doc->revision + 1));
        }
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
     * Test Simple Put Document
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
                'GET /api/v2/families/TST_F2_1',
                __DIR__ . "/Expects/TST_F2_1.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_2',
                __DIR__ . "/Expects/TST_F2_2.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1?fields=document.properties.all',
                __DIR__ . "/Expects/TST_F2_1_all.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_2?fields=document.properties.all',
                __DIR__ . "/Expects/TST_F2_2_all.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1?fields=family.structure',
                __DIR__ . "/Expects/TST_F2_1_struct.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_2?fields=family.structure',
                __DIR__ . "/Expects/TST_F2_2_struct.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1/documents/',
                __DIR__ . "/Expects/TST_F2_1_docs.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_2/documents/',
                __DIR__ . "/Expects/TST_F2_2_docs.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1/documents/TST_F21D0',
                __DIR__ . "/Expects/TST_F21D0.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_2/documents/TST_F22D2',
                __DIR__ . "/Expects/TST_F22D2.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1/documents/TST_F21D1',
                __DIR__ . "/Expects/TST_F21D1.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1/documents/TST_F21D1/revisions/0',
                __DIR__ . "/Expects/TST_F21D1_rev0.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1/documents/TST_F21D1/revisions/1',
                __DIR__ . "/Expects/TST_F21D1_rev1.json"
            ),
            array(
                'GET /api/v2/families/TST_F2_1/documents/TST_F21D1/revisions/3',
                __DIR__ . "/Expects/TST_F21D1_rev3.json"
            )
        );
    }

    public function dataPutDocument()
    {
        return array(

            array(
                'PUT /api/v2/documents/TST_F21D0',
                json_encode([
                    "document" => [
                        "attributes" => [
                            "tst_f2_title" => [
                                "value" => "Exposition universelle"
                            ],
                            "tst_f2_date" => [
                                "value" => "1889-05-06"
                            ]
                        ]
                    ]
                ]),

                __DIR__ . "/Expects/TST_F21D0_updated.json"
            ),

            array(
                'PUT /api/v2/documents/TST_F21D1',
                json_encode([
                    "document" => [
                        "attributes" => [
                            "tst_f2_title" => [
                                "value" => "Indépendance du Canada"
                            ],
                            "tst_f2_date" => [
                                "value" => "1857-07-01"
                            ]
                        ]
                    ]
                ]),

                __DIR__ . "/Expects/TST_F21D1_updated.json"
            ),

            array(
                'PUT /api/v2/documents/TST_F22D2',
                json_encode([
                    "document" => [
                        "attributes" => [
                            "tst_f2_title" => [
                                "value" => "Fondation de l’organisation humanitaire Médecins sans frontières"
                            ],
                            "tst_f2_date" => [
                                "value" => "1971-12-22"
                            ],
                            "tst_f2_timestamp" => [
                                "value" => "2000-01-01T12:30:00"
                            ]
                        ]
                    ]
                ]),

                __DIR__ . "/Expects/TST_F22D2_updated.json"
            )
        );
    }
}
