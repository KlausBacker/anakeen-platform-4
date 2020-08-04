<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\Exception;

/**
 * Class UserParameters
 *
 * @note Used by route : GET /api/v2/admin/parameters/users/{user}/
 * @package Anakeen\Routes\Admin\Parameters
 */
class UserParameters
{
    protected $user;
    protected $userDisplayValue;
    protected $outputGlobalParamResult;
    protected $outputUserParamResult;

    /**
     * Get user defined parameters and user definable parameters
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);

        $return = $this->doRequest();
        return ApiV2Response::withData($response, $return);
    }

    /**
     * Init parameters from request
     *
     * @param $args
     * @throws Exception
     */
    private function initParameters($args)
    {
        $this->user = AccountManager::getAccount($args["user"]);
        if ($this->user) {
            $this->userDisplayValue = [
                "userId" => $this->user->id,
                "login" => $this->user->login,
                "displayValue" => $this->user->getAccountName()
            ];
        } else {
            throw new Exception('USERPARAMETERS0001', $args["user"]);
        }
    }

    /**
     * Execute function to get all parameters for the user
     *
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    private function doRequest()
    {
        $rawFilteredParameters = [];
        $this->getDataFromDb();
        $rawFilteredParameters = $this->filterParameters();
        $treeListParameters["gridData"] = $this->formatParameters($rawFilteredParameters);
        $treeListParameters["user"] = $this->userDisplayValue;
        return $treeListParameters;
    }

    /**
     * - Get global parameters from database
     * - Get user parameters from database
     *
     * @throws \Anakeen\Database\Exception
     */
    private function getDataFromDb()
    {
        $sqlGlobalParamRequest = <<<SQL
select paramdef.*, paramv.val as value, paramv.type as usefor 
from paramdef, paramv 
where paramdef.isuser = 'Y' and  paramdef.name = paramv.name and paramv.type = 'G';
SQL;

        $sqlUserParamRequest = <<<SQL
select paramdef.*, paramv.val as value, paramv.type as usefor 
from paramdef, paramv 
where paramdef.isuser = 'Y' and  paramdef.name = paramv.name and paramv.type = 'U%d';
SQL;

        $sqlUserParamRequest = sprintf($sqlUserParamRequest, $this->user->id);
        $this->outputGlobalParamResult = [];
        $this->outputUserParamResult = [];

        DbManager::query($sqlGlobalParamRequest, $this->outputGlobalParamResult);
        DbManager::query($sqlUserParamRequest, $this->outputUserParamResult);
    }

    /**
     * Filter to unduplicate parameters (between global and user parameters)
     * @return array
     */
    private function filterParameters()
    {
        $data = [];

        foreach ($this->outputGlobalParamResult as $globalParam) {
            $globalParam['initialValue'] = $globalParam['value'];
            $globalParam['value'] = '';
            $globalParam['forUser'] = false;
            $data[$globalParam["name"]] = $globalParam;
        }
        foreach ($this->outputUserParamResult as $userParam) {
            $data[$userParam["name"]]["value"] = $userParam["value"];
            $data[$userParam["name"]]["forUser"] = true;
        }
        return $data;
    }

    /**
     * Correctly format the parameter to send in the response
     *
     * @param $parameters
     * @return array
     */
    private function formatParameters($parameters)
    {
        $allParameters = [];

        foreach ($parameters as $parameter) {
            $formatedParameter = [];

            $nsName = explode('::', $parameter['name'], 2);

            $formatedParameter['nameSpace'] = $nsName[0];
            $formatedParameter['name'] = $nsName[1];

            $formatedParameter['description'] = $parameter['descr'];
            $formatedParameter['category'] = $parameter['category'];

            $formatedParameter['value'] = $parameter['value'];
            $formatedParameter['initialValue'] = $parameter['initialValue'];

            $formatedParameter['isUser'] = ($parameter['isuser'] === 'Y');
            $formatedParameter['isGlobal'] = ($parameter['isglob'] ?? '' === 'Y');
            $formatedParameter['forUser'] = $parameter['forUser'];

            $formatedParameter['isStatic'] = ($parameter['kind'] === 'static');
            $formatedParameter['isReadOnly'] = ($parameter['kind'] === 'readonly');

            if (!$formatedParameter['isStatic'] && !$formatedParameter['isReadOnly']) {
                $formatedParameter['type'] = $parameter['kind'];
            } else {
                $formatedParameter['type'] = '';
            }

            $allParameters[] = $formatedParameter;
        }

        return $allParameters;
    }
}
