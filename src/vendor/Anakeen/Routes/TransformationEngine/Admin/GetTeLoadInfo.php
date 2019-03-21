<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Router\ApiV2Response;
use Anakeen\TransformationEngine\Client;

/**
 *
 * @use     by route GET /api/admin/transformationengine/load
 */
class GetTeLoadInfo
{

    protected $taskId;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
    }

    /**
     *
     * @return array
     */
    protected function doRequest()
    {
        return $this->getTeLoadInfo();
    }

    public function getTeLoadInfo()
    {
        $te = new Client();
        $te->retrieveServerInfo($info, true);
        return $info;
    }
}
