<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\DbManager;

class AlterParameter
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // Parameter to change
        $parameterName = $args['parameter_name'];

        // New value
        $newValue = $request->getParam('value');

        // Get current parameter to check new value's format
        $sqlQuery = '';
        $queryOutput = [];
        DbManager::query($sqlQuery, $queryOutput);

        // Check validity of the new value
    }
}