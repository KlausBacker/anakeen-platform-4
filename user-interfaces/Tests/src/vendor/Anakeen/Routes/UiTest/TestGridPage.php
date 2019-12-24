<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;
use Anakeen\Ui\UIGetAssetPath;

class TestGridPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestGridPage.html.mustache";
        $template = file_get_contents($page);
        $data = [
            "JS_DEPS" => [
                [
                    "key" => "kendo",
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
            "JS" => [],
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