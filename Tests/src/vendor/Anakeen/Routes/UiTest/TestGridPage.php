<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;

class TestGridPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestGridPage.html.mustache";
        $template = file_get_contents($page);
        $data = [
            "JS_DEPS" => [
                [
                    "key" =>"jquery",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" =>"kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" =>"ankcomponents",
                    "path" => \Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath()
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => \Dcp\Ui\UIGetAssetPath::getPolyfill()
                ],
                [
                    "key" =>"ankcomponents",
                    "path" => \Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath(true)
                ]
            ],
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCssKendo()
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}