<?php

namespace Control\Api;


use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\ModuleJob;

class GetModuleParameters
{
    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        $data = $this->getData();
        return $response->withJson($data);
    }

    protected function getData()
    {
        if (!ModuleJob::isReady()) {
            throw new ApiRuntimeException("Not initialized");
        }
        $data = Context::getParameters();
        return $data;
    }

}