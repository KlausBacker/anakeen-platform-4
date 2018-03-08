<?php

namespace Anakeen\Pu\Routes;

use Dcp\Core\ContextManager;

require_once __DIR__ . '/../TestCaseRoutes.php';
require DEFAULT_PUBDIR . '/vendor/Anakeen/lib/vendor/autoload.php';


class CoreRouteAccess extends TestCaseRoutes
{

    protected static function getCommonImportFile()
    {

        $importAccounts = new \Dcp\Core\ImportAccounts();
        $importAccounts->setFile(__DIR__ . "/Inputs/testAccounts.xml");
        $importAccounts->import();

        $import = array();
        $import[] = __DIR__ . "/Inputs/userAccess.csv";
        return $import;
    }

    /**
     * Test Simple Get Document
     *
     * @dataProvider dataGetRouteAccess
     *
     * @param $uri
     * @param $login
     * @param $expectedJsonFile
     *
     * @throws \Anakeen\Router\Exception
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetRouteAccess($uri, $login, $expectedJsonFile)
    {
        $account=new \Account();
        $account->setLoginName($login);
        ContextManager::sudo($account);
        $app = $this->setApiUriEnv($uri);

        $response = $app->run(true);
        $rawBody = (string)$response->getBody();
        $this->isJSONMatch($rawBody, file_get_contents($expectedJsonFile));
    }



    public function dataGetRouteAccess()
    {
        return array(
            "accessRouteAdmin" => array(
                'GET /tests/routes/',
                "test.admin",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessRouteUser1" => array(
                'GET /tests/routes/',
                "test.user1",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessRouteUser2" => array(
                'GET /tests/routes/',
                "test.user2",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessRouteUser3" =>  array(
                'GET /tests/routes/',
                "test.user3",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessRouteUser4" => array(
                'GET /tests/routes/',
                "test.user4",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessSecuritAdmin" => array(
                'GET /tests/routes/securit/',
                "test.admin",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessSecuritUser1" => array(
                'GET /tests/routes/securit/',
                "test.user1",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessSecuritUser2" => array(
                'GET /tests/routes/securit/',
                "test.user2",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessSecuritUser3" =>  array(
                'GET /tests/routes/securit/',
                "test.user3",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessSecuritUser4" => array(
                'GET /tests/routes/securit/',
                "test.user4",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessUserGuestAdmin" => array(
                'GET /tests/routes/userguest/',
                "test.admin",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessUserGuestUser1" => array(
                'GET /tests/routes/userguest/',
                "test.user1",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessUserGuestUser2" => array(
                'GET /tests/routes/userguest/',
                "test.user2",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessUserGuestUser3" =>  array(
                'GET /tests/routes/userguest/',
                "test.user3",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessUserGuestUser4" => array(
                'GET /tests/routes/userguest/',
                "test.user4",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessForAllAdmin" => array(
                'GET /tests/routes/forall/',
                "test.admin",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessForAllUser1" => array(
                'GET /tests/routes/forall/',
                "test.user1",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessForAllUser2" => array(
                'GET /tests/routes/forall/',
                "test.user2",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessForAllUser3" =>  array(
                'GET /tests/routes/forall/',
                "test.user3",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessForAllUser4" => array(
                'GET /tests/routes/forall/',
                "test.user4",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessGlobalAdmin" => array(
                'GET /tests/routes/global/',
                "test.admin",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessGlobalUser1" => array(
                'GET /tests/routes/global/',
                "test.user1",
                __DIR__ . "/Expects/routeAccess.json"
            ),

            "accessGlobalUser2" => array(
                'GET /tests/routes/global/',
                "test.user2",
                __DIR__ . "/Expects/routeAccess.json"
            ),
            "accessGlobalUser3" =>  array(
                'GET /tests/routes/global/',
                "test.user3",
                __DIR__ . "/Expects/routeAccessDeny.json"
            ),
            "accessGlobalUser4" => array(
                'GET /tests/routes/global/',
                "test.user4",
                __DIR__ . "/Expects/routeAccessDeny.json"
            )
        );
    }
}
