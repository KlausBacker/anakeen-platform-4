<?php

namespace Anakeen\Routes\Devel\UI;

class Main extends \Anakeen\Hub\Routes\Hub
{
    protected function getHubInstanceId(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        return "DEVELCENTER";
    }
}
