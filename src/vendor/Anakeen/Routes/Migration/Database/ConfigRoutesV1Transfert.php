<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class ConfigApplicationTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route POST /api/v2/migration/database/transfert/application/{application}
 */
class ConfigRoutesV1Transfert
{
    protected $applicationName;
    protected $structure;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters();
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters()
    {

        ///  Utils::importForeignTable('application');
        ///  Utils::importForeignTable('paramv');
    }

    protected function doRequest()
    {
        $data = [];

        $sql = sprintf("select val from dynacase.paramv where name='CRUD_CLASS' and appid = (select id from dynacase.application where name = 'HTTPAPI_V1')");


        $sql = sprintf("select val from paramv where name='CRUD_CLASS'");
        DbManager::query($sql, $rawRoutes, true, true);
        if (!$rawRoutes) {
            throw new Exception(sprintf("CRUD_CLASS  not found"));
        }
        $routeData = json_decode($rawRoutes, true);
        foreach ($routeData as $k => $routeDatum) {
            if (substr($routeDatum["class"], 0, 5) === "\\Dcp\\") {
                unset($routeData[$k]);
            }
        }


        self::transfertRoutes($routeData);

        return $data;
    }

    protected static function transfertRoutes($routesData)
    {

        $template = file_get_contents(__DIR__ . '/../../../Migration/RouteV1.xml.mustache');
        foreach ($routesData as &$routesDatum) {
            $paths = explode("\\", $routesDatum["class"]);
            $routesDatum["classPath"] = array_filter($paths, function ($a) {
                return !empty($a);
            });

            $routesDatum["classSubDir"] = implode("/", $routesDatum["classPath"]);
            $routesDatum["name"] = implode("-", $routesDatum["classPath"]);

            $routesDatum["pattern"] = self::regExpToFastRoute($routesDatum["regExp"]);
            $routesDatum["className"] = array_pop($routesDatum["classPath"]);
            $routesDatum["classSubNs"] = implode("\\", $routesDatum["classPath"]);
        }

        /**
         * [order] => 100
         * [class] => \Dcp\HttpApi\V1\Crud\UserTagCollection
         * [regExp] => %^/families/(?P<familyId>[^/]+)/documents/(?P<identifier>[^/]+)/usertags/$%
         * [description] => User Tags of the document <documentId> of the family <familyId>
         * [canonicalURL] => families/<familyId>/documents/<documentId>/usertags/
         */
        $data["VENDOR"] = ContextManager::getParameterValue("Migration", "VENDOR");
        $data["MODULE"] = ContextManager::getParameterValue("Migration", "MODULE");
        $data["routes"] = array_values($routesData);


        $configDir = sprintf("%s/%s/Config/Routes", $data["VENDOR"], $data["MODULE"]);
        $routeConfigPath = sprintf("%s/vendor/%s/apiv1.xml", ContextManager::getRootDirectory(), $configDir);
        $mustache = new \Mustache_Engine();
        $routeConfigContent = $mustache->render($template, $data);
        Utils::writeFileContent($routeConfigPath, $routeConfigContent);


        $stubTemplateAction = file_get_contents(__DIR__ . '/../../../Migration/RouteV1.php.mustache');

        // Write Route PHP class stubs : one by action
        foreach ($routesData as $result) {
            $routeAppPath = sprintf(
                "%s/vendor/%s/%s/Routes/V1/%s.php",
                ContextManager::getRootDirectory(),
                $data["VENDOR"],
                $data["MODULE"],
                $result["classSubDir"]
            );
            $result["VENDOR"] = $data["VENDOR"];
            $result["MODULE"] = $data["MODULE"];
            print "$routeAppPath\n";

            $routeConfigContent = $mustache->render($stubTemplateAction, $result);
            Utils::writeFileContent($routeAppPath, $routeConfigContent);
        }

        print "$routeConfigPath\n";
    }

    protected static function regExpToFastRoute($regexp)
    {
        /*                        [regExp] => %^/documents/(?P<identifier>[^\/]+)/views/!bdl(?P<viewIdentifier>[^/]+)$%
     */
        //print_r($matches);
        $pattern = preg_replace_callback('/\(([^\)]+)\)/', function ($var) use ($regexp) {

            if (preg_match('/^\?P<([^>]*)>\[\^\/\][\+|\*]/', $var[1], $match)) {
                //print_r($match);
                return '{' . $match[1] . '}';
            } elseif (preg_match('/^\?P<([^>]*)>(.*)/', $var[1], $match)) {
                print_r([$regexp, $var, $match]);

                return sprintf('{%s:%s}', $match[1], $match[2]);
            }
            return $var[0];
        }, $regexp);

        return $pattern;
    }
}
