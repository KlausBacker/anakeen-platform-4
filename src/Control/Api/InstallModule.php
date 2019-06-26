<?php

namespace Control\Api;

use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\LibSystem;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;

/**
 * Class Install
 *
 * @note route : POST /control/api/modules/{name}
 */
class InstallModule extends Install
{

    protected $moduleName;


    protected function initModuleManager(\Slim\Http\Request $request, $args)
    {
        $this->moduleName=$args["name"];
        $this->moduleManager = new ModuleManager($this->moduleName);
    }
    protected function execute__()
    {
        $data=[];
        if (ModuleJob::isRunning()) {
            throw new ApiRuntimeException(sprintf("Job is already in progress. Wait or kill it"));
        }
        if (!Context::getRepositories(true)) {
            throw new ApiRuntimeException(sprintf("No one repositories configured. Use \"registry\" command to add."));
        }
          $module = new ModuleManager($this->moduleName);

        if (!$module->prepareInstall($this->force)) {
            $data["message"]="No modules to install. All is up-to-date.";
        } else {
            $context=Context::getContext();
            if ($context->warningMessage) {
                $data["warning"]= $context->warningMessage;
            }

            //AskParameters::askParameters($module, $this->getHelper('question'), $input, $output);
            $module->recordJob(false);
            LibSystem::purgeTmpFiles();
            $data["message"]="Job Recorded";
        }
        return $data;
    }

}