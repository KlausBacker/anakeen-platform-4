<?php

namespace Control\Api;


use Control\Exception\ApiRuntimeException;
use Control\Internal\ModuleJob;

class Search
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
        $data =  \Control\Internal\Info::getAllModuleList();;
        return $data;
    }

}