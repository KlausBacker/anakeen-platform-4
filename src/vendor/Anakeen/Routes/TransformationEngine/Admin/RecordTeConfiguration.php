<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\Lib\ApiMessage;

/**
 *
 * @use     by route PUT /api/admin/transformationengine/config/
 */
class RecordTeConfiguration
{

    protected $config;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request);

        $data = $this->doRequest();
        $msg = new ApiMessage("TE configuration recorded");
        return ApiV2Response::withData($response, $data, [$msg]);
    }


    protected function initParameters(\Slim\Http\request $request)
    {
        $this->config = $request->getParsedBody();
    }

    /**
     *
     * @return array
     */
    protected function doRequest()
    {
        foreach ($this->config as $name => $value) {
            if ($name === "TE_ACTIVATE") {
                $value = ($value === true) ? "yes" : "no";
            }
            ContextManager::setParameterValue("TE", $name, $value);
        }

        return GetTeConfiguration::getTeConfig();
    }
}
