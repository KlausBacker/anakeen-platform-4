<?php

namespace Anakeen\App\Core;

use Anakeen\Core\AssetManager;
use Anakeen\Core\ContextManager;

/**
 * Class WelcomePage
 *
 * Welcome Page
 * @note Used by route : GET /
 * @package Anakeen\Routes\Core
 */
class WelcomePage
{

    /**
     * Return Welcome page
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
        $templateFile=__DIR__."/welcomePage.mustache.html";

        $mustache=new \Mustache_Engine();

        $data["cssRef"]= AssetManager::getAssetLink(__DIR__."/WelcomePage.css");
        $data["thisyear"]= strftime("%Y", time());
        $data["version"]= \Anakeen\Core\ContextManager::getApplicationParam("VERSION");
        $data["userRealName"]= ContextManager::getCurrentUser()->getAccountName();
        $data["userDomain"]= \Anakeen\Core\ContextManager::getApplicationParam("CORE_CLIENT");
        $data["isAdmin"]= is_dir(sprintf("%s/AdminCenter", PUBLIC_DIR)); //Test to detect admin center

        $out=$mustache->render(file_get_contents($templateFile), $data);
        return $response->write($out);
    }
}
