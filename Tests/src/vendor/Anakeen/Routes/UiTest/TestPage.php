<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;

class TestPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestPage.html";
        $template = file_get_contents($page);
        $version = \Dcp\Ui\UIGetAssetPath::getWs();
        $data = [
            "JS" => [
                [
                    "key" =>"jquery",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" =>"kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" =>"ank-components",
                    "path" => \Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath()
                ],
                [
                    "key" => "test-page",
                    "path" =>  \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("apps/uitest/dist/TestPage.js")
                ]
            ],
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/document/bootstrap.css")
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/document/kendo.css")
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}