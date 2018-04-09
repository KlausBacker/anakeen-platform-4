<?php

namespace Anakeen\App\Core;

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
     * Return all visible documents
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
        $page=__DIR__."/welcomePage.layout.html";
        $action=ContextManager::getCurrentAction();

        $action->lay=new \Layout($page, $action);

        $action->parent->AddCssRef("CORE:welcomePage.css");
        $action->lay->set("thisyear", strftime("%Y", time()));
        $action->lay->set("version", $action->GetParam("VERSION"));
        $action->lay->set("userRealName", $action->user->firstname . " " . $action->user->lastname);
        $action->lay->set("userDomain", \Anakeen\Core\ContextManager::getApplicationParam("CORE_CLIENT"));
        $action->lay->set("isAdmin", (file_exists('admin/index.php') && $action->canExecute("CORE_ADMIN_ROOT", "CORE_ADMIN") === ''));

        return $response->write($action->lay->gen());
    }
}
