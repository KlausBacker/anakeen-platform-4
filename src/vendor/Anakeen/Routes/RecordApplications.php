<?php

namespace Anakeen\Routes\Core;

use Dcp\Router\ApiV2Response;

/**
 * Class RecordApplications
 *
 * Record applications to database
 *
 * @note    Used by route : POST /api/v2/admin/applications/
 * @package Anakeen\Routes\Core
 */
class RecordApplications
{

    /**
     * Record applications to database
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param array               $args
     *
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $data = [];
        $data["apps"] = $this->recordApplications();
        return ApiV2Response::withData($response, $data);
    }

    /**
     * @return \Anakeen\Router\AppInfo[]
     * @throws \Dcp\Core\Exception
     */
    public function recordApplications()
    {
        $routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
        $routeConfig->recordApps();
        return $routeConfig->getApps();
    }
}
