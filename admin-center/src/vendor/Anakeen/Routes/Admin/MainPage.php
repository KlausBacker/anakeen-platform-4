<?php

namespace Anakeen\Routes\Admin;


use Anakeen\Core\ContextManager;

class MainPage
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page=__DIR__."/../../AdminCenter/Layout/adminCenterMainPage.html";

        return $response->write(file_get_contents($page));
    }

}