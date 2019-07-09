<?php

namespace Control\Api;


use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;

class AddRegistry
{
    protected $name;
    protected $url;

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response ,array $args)
    {
        $this->name= $args["name"];
        $this->url=$request->getParam("url");
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
        if (!$this->url) {
            throw new InvalidArgumentException(sprintf("Url argument is needed for add action"));
        }
        Context::addRepository($this->name, $this->url);
        $data =   $repo = Context::getRepositories();
        foreach ($data as $datum) {
            if ($datum->name === $this->name) {
                return $datum;
            }
        }
        return [];
    }

}