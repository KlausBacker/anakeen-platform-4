<?php
namespace Anakeen\Pu\Routes\CoreDocument;

class PuFieldAccess extends \Anakeen\Pu\Routes\TestCaseRoutes
{
    /**
     * import TST_APIBASE family
     *
     * @static
     * @return string|string[]
     */
    protected static function getCommonImportFile()
    {
        self::importAccountFile(__DIR__ . "/Inputs/tst_account.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/tst_f02.struct.xml");


        $import = array();
        $import[] = __DIR__ . "/Inputs/docsfa.xml";
        return $import;
    }


    /**
     * Test Simple Get Document
     *
     * @dataProvider dataGetDocument
     *
     * @param $uri
     * @param $login
     * @param $expectedJsonFile
     *
     * @throws \Anakeen\Router\Exception
     * @throws \Dcp\Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetDocument($uri, $login, $expectedJsonFile)
    {
        self::sudo($login);
        $app = $this->setApiUriEnv($uri);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
        self:self::exitSudo();
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
    public function __testPutDocument($uri, $postContent, $expectedJsonFile)
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
                'GET /api/v2/documents/TST_FA1',
                "admin",
                __DIR__ . "/Expects/docTstFaAdmin.json"
            ),
            array(
                'GET /api/v2/documents/TST_FA1',
                "fachewie",
                __DIR__ . "/Expects/docTstFaChewie.json"
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
}
