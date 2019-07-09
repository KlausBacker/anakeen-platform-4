<?php

namespace Control\Api;

use Control\Internal\ModuleJob;

class Status
{

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        $data = $this->getData();
        return $response->withJson($data);
    }

    protected function getData()
    {
        return ModuleJob::getJobStatus();
    }

}