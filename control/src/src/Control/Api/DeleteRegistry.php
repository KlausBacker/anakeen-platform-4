<?php

namespace Control\Api;

use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;

class DeleteRegistry
{
    protected $name;

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
    {
        $this->name = $args["name"];
        $data = $this->getData();
        return $response->withJson($data);
    }

    protected function getData()
    {
        if (!ModuleJob::isReady()) {
            throw new ApiRuntimeException("Not initialized");
        }

        if (ModuleJob::isRunning()) {
            throw new RuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }
        if (!$this->name) {
            throw new InvalidArgumentException(sprintf("Name argument is needed for add action"));
        }

        Context::removeRepository($this->name);
        return [];
    }

}