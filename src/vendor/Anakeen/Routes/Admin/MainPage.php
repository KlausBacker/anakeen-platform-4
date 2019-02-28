<?php

namespace Anakeen\Routes\Admin;

class MainPage extends \Anakeen\Hub\Routes\Hub
{
    protected function getHubInstanceId(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        return "ADMINCENTER";
    }
}
