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
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     * @throws \Anakeen\Ui\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/LoginPage.html.mustache";
        $template = file_get_contents($page);
        $data = [
            "title" => sprintf(Gettext::___("Connexion to %s", "login"), ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_CLIENT")),
            "JS_DEPS" => [
                [
                    "key" => "polyfill",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getJsPolyfill(),
                    "noModule" => true
                ],
                [
                    "key" => "kendo",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" => "kendoDLL",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getJSKendoComponentPath()
                ],
                [
                    "key" => "vueDll",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getJSVueComponentPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "login",
                    "path" =>  \Anakeen\Ui\UIGetAssetPath::getElementAssets("ank-components", \Anakeen\Ui\UIGetAssetPath::isInDebug() ? "dev" : "prod")["login"]["js"]
                ],
            ],
            "JS_LEGACY" => [
                [
                    "key" => "login",
                    "path" =>  \Anakeen\Ui\UIGetAssetPath::getElementAssets("ank-components", \Anakeen\Ui\UIGetAssetPath::isInDebug() ? "dev" : "legacy")["login"]["js"]
                ],
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
                ],
                [
                    "key" => "login",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCss("login")
                ],
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}