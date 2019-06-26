<?php

namespace Control\Api;

use Control\Exception\ApiRuntimeException;
use Control\Internal\Context;
use Control\Internal\ModuleJob;
use Control\Internal\ModuleManager;

/**
 *
 * @note route : PUT /control/api/modules/
 */
class Update extends Install
{

    const nothingToDoMsg="No modules to update. All is up-to-date.";
    protected function prepareModuleManager()
    {
        return $this->moduleManager->prepareUpgrade($this->force);
    }
}