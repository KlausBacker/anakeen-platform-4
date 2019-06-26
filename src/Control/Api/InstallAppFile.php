<?php

namespace Control\Api;

use Control\Cli\AskParameters;
use Control\Internal\LibSystem;
use Control\Internal\ModuleManager;

/**
 * Class Install
 *
 * @note route : POST /control/api/modules
 */
class InstallAppFile extends Install
{
    protected $fileModulePath;
    protected $force = true;

    protected function initModuleManager(\Slim\Http\Request $request, $args)
    {
        $this->fileModulePath = LibSystem::tempnam(null, "ank-app-download");
        $body = $request->getBody();
        $tmp = fopen($this->fileModulePath, "w+");
        while (!$body->eof()) {
            fputs($tmp, $body->read(1024));
        }
        fclose($tmp);
        $this->moduleManager = new ModuleManager("");

        $this->moduleManager->setFile($this->fileModulePath);
    }

    protected function completeModuleManager()
    {
        foreach ($this->moduleParameters as $key => $value) {
            $this->moduleManager->setParameterAnswer($this->moduleManager->getName(), $key, $value);
        }

        AskParameters::setParameters($this->moduleManager->getAllParameters());
    }


}