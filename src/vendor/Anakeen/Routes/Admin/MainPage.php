<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 10/04/18
 * Time: 10:39
 */

namespace Anakeen\Routes\Admin;


use Anakeen\Core\ContextManager;

class MainPage
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page=__DIR__."/../../AdminCenter/Layout/adminCenterMainPage.html";
        $action=ContextManager::getCurrentAction();

        $action->lay=new \Layout($page, $action);

        return $response->write($action->lay->gen());
    }

}