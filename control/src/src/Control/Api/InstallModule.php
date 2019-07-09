<?php

namespace Control\Api;

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
        $this->moduleName = $args["name"];
        $this->moduleManager = new ModuleManager($this->moduleName);
    }

}