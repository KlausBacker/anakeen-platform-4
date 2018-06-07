<?php

namespace Anakeen\Pu\Routes;

use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\RequestBody;

class TestCaseRoutes extends \Dcp\Pu\TestCaseDcpCommonFamily
{
    protected static $importCsvEnclosure = '"';
    protected static $importCsvSeparator = ";";

    /**
     * @var \Slim\App
     */
    protected static $routerApp;
    protected $jsonResultFile;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
        $routes = $routeConfig->getRoutes();
        $middleWares = $routeConfig->getMiddlewares();

        self::$routerApp = \Anakeen\Router\RouterManager::getSlimApp();
        \Anakeen\Router\RouterManager::addRoutes($routes);
        // Add middlewares to the application
        \Anakeen\Router\RouterManager::addMiddlewares($middleWares);
    }

    protected static function getRouterApp()
    {
        return self::$routerApp;
    }

    protected static function setApiEnv(Environment $env)
    {
        /**
         * @var \Slim\Http\Request $request
         */
        $request = Request::createFromEnvironment($env);

        if (!empty($env["CONTENT"])) {
            $body = new RequestBody();
            $body->write($env["CONTENT"]);
            $body->rewind();
            $request = $request->withBody($body);
        }
        self::$routerApp->getContainer()['request'] = $request;


        /**
         * @var \Slim\Http\Response $response
         */
        $response = self::$routerApp->getContainer()['response'];
        if ($response) {
            $response->getBody()->rewind();
        }

        return self::$routerApp;
    }


    /**
     * Init Router with uri
     *
     * @param string $uri like "GET /api/v2/foo?a=3"
     * @param string $jsonContent
     *
     * @return \Slim\App
     * @throws Exception
     */
    protected static function setApiUriEnv($uri, $jsonContent = null)
    {
        if (!preg_match("/([A-Z]+)\s+(.*)/", $uri, $reg)) {
            throw new Exception("Syntax error in uri : $uri");
        }
        $method = $reg[1];
        $uri = $reg[2];
        $url = parse_url($uri);
        if (!$url) {
            throw new Exception("Syntax error in uri (url) : $uri");
        }

        $envConfig = [
            'REQUEST_METHOD' => $method,
            'HTTP_ACCEPT' => 'application/json',
            'REQUEST_URI' => $url["path"]
        ];
        if (!empty($url["query"])) {
            $envConfig['QUERY_STRING'] = $url["query"];
        }
        if (!empty($jsonContent)) {
            $envConfig['CONTENT_TYPE'] = 'application/json;charset=utf8';
            $envConfig['CONTENT'] = $jsonContent;
        }

        $env = Environment::mock($envConfig);
        return self::setApiEnv($env);
    }

    protected static function resetDocumentCache()
    {
        parent::resetDocumentCache();
        SEManager::cache()->clear();
    }

    /**
     * Verify data coherence between API DATA and expectedValues
     *
     * @param        $data
     * @param        $expectedValues
     * @param string $keys
     */
    protected function verifyData(array $data, array $expectedValues, $keys = "")
    {
        foreach ($expectedValues as $currentKey => $expectedValue) {
            $this->assertArrayHasKey(
                $currentKey,
                $data,
                sprintf(
                    'Unable to find the key "%s" for "%s" [api result : %s] // [expected : %s]. See file "%s".',
                    $currentKey,
                    $keys,
                    var_export($data, true),
                    var_export($expectedValues, true),
                    $this->jsonResultFile
                )
            );
            if (is_array($expectedValue)) {
                $nextKey = $keys . (empty($keys) ? $currentKey : ".$currentKey");
                $this->verifyData($data[$currentKey], $expectedValue, $nextKey);
            } else {
                if ($expectedValue === "%isoDate%") {
                    $this->assertGreaterThan(
                        0,
                        preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/", $data[$currentKey]),
                        "wrong date value for \"$keys.$currentKey\"   [api result : "
                        . var_export($data[$currentKey], true)
                        . " ] // [expected an iso date YYYY-MM-DDTHH:MM:SS ]"
                    );
                } elseif (preg_match('{^%regexp%(?P<regexp>.*)$}', $expectedValue, $m) === 1) {
                    $this->assertRegExp(
                        $m['regexp'],
                        $data[$currentKey],
                        sprintf(
                            'Wrong value for key "%s.%s" [api result : %s] // [expected (regexp match) : %s].' . "\n"
                            . 'See file "%s"',
                            $keys,
                            $currentKey,
                            var_export($data[$currentKey], true),
                            var_export($m['regexp'], true),
                            $this->jsonResultFile
                        )
                    );
                } else {
                    $this->assertEquals(
                        $expectedValue,
                        $data[$currentKey],
                        sprintf(
                            'Wrong value for key "%s.%s" [api result : %s] // [expected : %s].' . "\n"
                            . 'See file "%s"',
                            $keys,
                            $currentKey,
                            var_export($data[$currentKey], true),
                            var_export($expectedValue, true),
                            $this->jsonResultFile
                        )
                    );
                }
            }
        }
    }

    protected function isJSONMatch($jsonResult, $expectedResult)
    {
        $result = json_decode($jsonResult, true);
        $this->assertNotEmpty($result, sprintf("Fail decode result json : %s", $jsonResult));


        $expectedResult = str_replace('%baseURL%', URLUtils::getBaseURL(), $expectedResult);
        $expectedResult = str_replace('%userName%', ContextManager::getCurrentUser()->getAccountName(), $expectedResult);
        $expectedResult = str_replace('%userDocName%', SEManager::getTitle(ContextManager::getCurrentUser()->fid), $expectedResult);
        $expected = json_decode($expectedResult, true);
        $this->assertNotEmpty($expected, sprintf("Fail decode expected json : %s", $expectedResult));

        $this->jsonResultFile = $this->writeJSON($jsonResult);
        $this->verifyData($result, $expected);
    }

    protected function writeJSON($jsonString)
    {
        $file = sprintf("%s/%s.json", ContextManager::getTmpDir(), uniqid("testRoute"));
        $content = json_encode(json_decode($jsonString), JSON_PRETTY_PRINT);
        file_put_contents($file, $content ? $content : $jsonString);
        return $file;
    }
}
