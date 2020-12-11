<?php

namespace Anakeen\Routes\About;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;

class Version
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function doRequest()
    {
        $version = ContextManager::getParameterValue("About", "VERSION");
        $commitDate = ContextManager::getParameterValue("About", "COMMIT_DATE");
        return ["version" => $version, "commitDate" => $commitDate];
    }
}
