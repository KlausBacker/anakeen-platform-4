<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;
use Anakeen\Ui\UIGetAssetPath;

class TestAllTypeGridPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestAllTypeGridPage.html.mustache";
        $template = file_get_contents($page);
        $data = [
            "JS_DEPS" => [
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" =>"ankcomponents",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getSmartWebComponentsPath()
                ]
            ],
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCssKendo()
                ],
                [
                    "key" => "components",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCssSmartWebComponents()
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}