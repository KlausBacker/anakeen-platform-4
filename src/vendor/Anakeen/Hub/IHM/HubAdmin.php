<?php

namespace Anakeen\Hub\IHM;

class HubAdmin
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page=__DIR__."/hubAdmin.html";
        $mustache = new \Mustache_Engine();
        $data = [
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/theme/bootstrap.min.css")
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/theme/kendo.min.css")
                ],
                [
                    "key" => "components",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/theme/components.min.css")
                ],
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
