<?php

namespace Control\Api;


use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;

class SetInternalParameter
{
    protected $name;
    protected $value;

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
    {
        $this->name = $args["name"];
        $this->value = $request->getParam("value");
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
            throw new InvalidArgumentException(sprintf("Name argument is needed to set parameter"));
        }
        if ($this->value === null) {
            throw new InvalidArgumentException(sprintf("Value argument is needed to set parameter"));
        }
        $controlParameters = Context::getControlParameters();
        if (!isset($controlParameters[$this->name])) {
            throw new RuntimeException(sprintf("Internal parameter \"%s\" not found", $this->name));
        }
        Context::setControlParameter($this->name, $this->value);
        $results = Context::getControlParameters();
        return $results;
    }

}