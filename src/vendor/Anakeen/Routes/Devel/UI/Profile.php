<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 10/10/18
 * Time: 15:16
 */

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Router\Exception;
use Anakeen\SmartElementManager;
use Dcp\Ui\UIGetAssetPath;

class Profile
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/Layout/profile.html.mustache";
        $id = $args["id"];
        $se = SmartElementManager::getDocument($id);
        if (!$se->isAlive()) {
            throw new Exception("There is not smart element here $id");
        }
        $profileId = $se->profid;
        $mustache = new \Mustache_Engine();
        $data = [
            "title" => $se->getTitle(),
            "profileId" => $profileId,
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getCssKendo()
                ],
                [
                    "key" => "components",
                    "path" => UIGetAssetPath::getCssSmartWebComponents()
                ],
            ],
            "JS_DEPS" => [
                [
                    "key" =>"jquery",
                    "path" => UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" =>"kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "admin",
                    "path" => UIGetAssetPath::getElementAssets(
                        "developmentCenter",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["profile"]["js"]
                ]
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
