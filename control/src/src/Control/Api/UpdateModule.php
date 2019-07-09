<?php

namespace Control\Api;

use Control\Internal\ModuleManager;

/**
 *
 * @note route : PUT /control/api/modules/{name}
 */
class UpdateModule extends Update
{
    protected $moduleName;

    protected function initModuleManager(\Slim\Http\Request $request, $args)
    {
        $this->moduleName = $args["name"];
        $this->moduleManager = new ModuleManager($this->moduleName);
    }

}