<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class RestoreUserParameterSystemValue
 *
 * @note : Used by route : DELETE /api/v2/admin/parameters/{user}/{name_space}/{parameter_name}/
 * @package Anakeen\Routes\Admin\Parameters
 */
class RestoreUserParameterSystemValue
{
    protected $userLogin;
    protected $userId;
    protected $nameSpace;
    protected $parameterName;

    /**
     * Restore the systeml value for a user defined parameter value
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);

        $this->restoreParameter($this->nameSpace, $this->parameterName, $this->userId);
        return ApiV2Response::withData($response, ["Value" => ""]);
    }

    /**
     * Init parameters from request
     *
     * @param $args
     */
    private function initParameters($args)
    {
        $this->userId = AccountManager::getIdFromLogin($args["user"]);

        $this->nameSpace = $args['name_space'];
        $this->parameterName = $args['parameter_name'];
    }

    /**
     * Restore the system value for a user defined parameter value
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $userId
     */
    private function restoreParameter($nameSpace, $parameterName, $userId)
    {
        ContextParameterManager::setUserValue($nameSpace, $parameterName, null, $userId);
    }
}
