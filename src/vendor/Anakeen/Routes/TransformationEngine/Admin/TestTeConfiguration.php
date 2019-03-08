<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;

/**
 *
 * @use     by route PUT /api/admin/transformationengine/tests/{id}
 */
class TestTeConfiguration
{

    protected $key;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    /**
     * @param \Slim\Http\request $request
     * @param                    $args
     */
    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->key = intval($args["id"]);
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    protected function doRequest()
    {

        $data = [];

        ContextManager::getSession()->register(CheckTeConfiguration::CheckId, $this->key + 1);

        $data["key"] = $this->key;

        return $data;
    }
}
