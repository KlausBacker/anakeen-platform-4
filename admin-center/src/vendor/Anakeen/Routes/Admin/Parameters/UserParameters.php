<?php

namespace Anakeen\Routes\Admin\Parameters;


use Anakeen\Core\DbManager;
use Dcp\Db\Exception;

class UserParameters
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $sqlRequest = 'select paramdef.*, paramv.val as value, paramv.type as usefor, application .name as domain  from paramdef, paramv, application where paramv.appid= application.id and paramdef.name = paramv.name;';
        $outputResult = [];

        try {
            DbManager::query($sqlRequest, $outputResult);
        } catch (Exception $e) {
        }

    }
}