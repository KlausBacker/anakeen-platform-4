<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Components\Authent\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Utils\Gettext;

/**
 * Main page to login
 *
 * Class LoginPage
 *
 * @note    Used by route : GET /login/
 * @package Anakeen\Routes\Authent
 */
class LoginPage
{
    /**
     * Return html page to login
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/LoginPage.html";
        $template = file_get_contents($page);
        $data = [
            "title" => sprintf(Gettext::___("Connexion to %s", "login"), ContextManager::getParameter("CORE_CLIENT")),
            "JS" => [
                [
                    "key" => "jquery",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" => "ank-components",
                    "path" => \Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath()
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
                ],
                [
                    "key" => "login",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCustomAssetPath("/css/ank/document/login.css")
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}