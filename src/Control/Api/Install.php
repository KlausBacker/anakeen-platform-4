<?php

namespace Control\Api;

use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;

/**
 * Class Install
 *
 * @note route : POST /control/api/modules/
 */
class Install
{

    protected $moduleParameters;
    /**
     * @var bool
     */
    protected $force = false;
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args = [])
    {
        if ($request->getQueryParam("force")) {
            $this->force = true;
        }
        $this->moduleParameters = $request->getQueryParams();
        $this->verifyStatus();
        $this->initModuleManager($request, $args);
        $data = $this->execute();
        return $response->withJson($data);
    }

    protected function verifyStatus()
    {
        if (ModuleJob::isRunning()) {
            throw new ApiRuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }
        if (!Context::getRepositories(true)) {
            throw new ApiRuntimeException(sprintf("No one repositories configured. Use \"registry\" command to add."));
        }

    }

    protected function initModuleManager(/** @noinspection PhpUnusedParameterInspection */ \Slim\Http\Request $request, $args)
    {
        $this->moduleManager = new ModuleManager("");
    }

    protected function completeModuleManager()
    {
        // update in child classes
    }

    protected function execute()
    {
        $data = [];

        if (!$this->moduleManager->prepareInstall($this->force)) {
            $data["message"] = "No modules to install. All is up-to-date.";
        } else {
            $context = Context::getContext();
            if ($context->warningMessage) {
                $data["warning"] = $context->warningMessage;
            }

            $this->completeModuleManager();
            $this->moduleManager->recordJob(false);

            $data["info"] = $this->getInfo();
            $data["message"] = "Job Recorded";
        }
        return $data;
    }

    protected function getInfo()
    {
        $info = [];
        foreach ($this->moduleManager->getDepencies() as $module) {
            $info[] = [
                "name" => $module->name,
                "description" => $module->description,
                "phase" => $module->needphase
            ];
        }
        return $info;
    }

}