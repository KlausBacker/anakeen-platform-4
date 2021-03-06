<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Ui\UIGetAssetPath;

class Hub
{

    protected function getHubInstanceId(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        if (isset($args["hubId"])) {
            return $args["hubId"];
        }
        throw new Exception("The Hub Instance ID is not defined");
    }

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Ui\Exception
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        $page = __DIR__ . "/Layout/hub.html.mustache";
        $hubId = $this->getHubInstanceId($request, $response, $args);
        $mustache = new \Mustache_Engine();
        $hub=SEManager::getDocument($hubId);
        $title = $hub->getTitle();
        $data = [
            "title" => $title,
            "icon" => $hub->getIcon(),
            "hubInstanceId" => $hubId,
            "JS_DEPS" => [
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "hub",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hub"]["js"]
                ]
            ],
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
                    "key" => "component",
                    "path" => UIGetAssetPath::getCssSmartWebComponents()
                ],
                [
                    "key" => "smartElement",
                    "path" => UIGetAssetPath::getCssSmartElement()
                ]
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
