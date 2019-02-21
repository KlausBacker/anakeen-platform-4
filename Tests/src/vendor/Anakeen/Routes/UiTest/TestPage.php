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
                    "path" => \Anakeen\Ui\UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" =>"kendo",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" =>"ankcomponents",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getSmartWebComponentsPath()
                ],
                [
                    "key" => "testPage",
                    "path" =>  \Anakeen\Ui\UIGetAssetPath::getElementAssets("uiTest")["TestPage"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getPolyfill()
                ],
                [
                    "key" =>"ankcomponents",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getSmartWebComponentsPath(true)
                ],
                [
                    "key" => "testPage",
                    "path" =>  \Anakeen\Ui\UIGetAssetPath::getElementAssets("uiTest", "prod")["TestPage"]["js"]
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
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}