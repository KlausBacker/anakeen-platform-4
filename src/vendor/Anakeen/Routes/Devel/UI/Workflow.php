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
use Anakeen\Ui\UIGetAssetPath;

class Workflow
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/Layout/workflow.html.mustache";
        $id = $args["id"];
        $se = SmartElementManager::getDocument($id);
        if (!$se->isAlive()) {
            throw new Exception("There is not smart element here $id");
        }
        $workflowId = $id;
        $mustache = new \Mustache_Engine();
        $data = [
            "title" => $se->getTitle(),
            "workflowIdentifier" => $workflowId,
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
                    "key" =>"kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" => "kendoDLL",
                    "path" => UIGetAssetPath::getJSKendoComponentPath()
                ],
                [
                    "key" => "vueDll",
                    "path" => UIGetAssetPath::getJSVueComponentPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "admin",
                    "path" => UIGetAssetPath::getElementAssets(
                        "developmentCenter",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["workflow"]["js"]
                ]
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
