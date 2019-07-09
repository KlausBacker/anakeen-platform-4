<?php

namespace Control\Api;


use Control\Exception\ApiRuntimeException;
use Control\Internal\ModuleJob;

class ShowModule
{
    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $data = $this->getData($args["name"]);
        if (!$data) {
            $response = $response->withStatus(404, "Module not found");
        }
        return $response->withJson($data);
    }

    protected function getData($moduleName)
    {
        if (!ModuleJob::isReady()) {
            throw new ApiRuntimeException("Not initialized");
        }

        $data = \Control\Internal\Info::getInstalledModuleList();
        foreach ($data as $datum) {
            if ($datum->name === $moduleName) {
                return $datum;
            }
        }
        return [];
    }

}