<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Router\ApiV2Response;

class ProfileTransfert
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
    }

    protected function doRequest()
    {
        $data=[];
        return $data;
    }
}
