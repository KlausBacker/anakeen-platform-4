<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;

class TestPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestPage.html.mustache";
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
                ],
                [
                    "key" => "testPage",
                    "path" =>  \Dcp\Ui\UIGetAssetPath::getElementAssets("uiTest")["TestPage"]["js"]
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
                ],
                [
                    "key" => "testPage",
                    "path" =>  \Dcp\Ui\UIGetAssetPath::getElementAssets("uiTest", "legacy")["TestPage"]["js"]
                ]
            ],
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/theme/bootstrap.min.css")
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/theme/kendo.min.css")
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}