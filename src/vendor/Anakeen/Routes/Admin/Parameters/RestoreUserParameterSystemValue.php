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

        try {
            $this->restoreParameter($this->nameSpace, $this->parameterName, $this->userId);
            return ApiV2Response::withData($response, ['success' => 'true']);
        } catch (\Exception $e) {
            $response->withStatus(500, 'Error during value restoration');
            return ApiV2Response::withMessages($response, ['Error during value restoration']);
        }
    }

    /**
     * Init parameters from request
     *
     * @param $args
     */
    private function initParameters($args)
    {
        $this->userLogin = $args['user'];
        $this->userId = AccountManager::getIdFromLogin($this->userLogin);

        $this->nameSpace = $args['name_space'];
        $this->parameterName = $args['parameter_name'];
    }

    /**
     * Restore the system value for a user defined parameter value
     *
     * @param $nameSpace
     * @param $parameterName
     * @param $userId
     * @throws \Dcp\Exception
     */
    private function restoreParameter($nameSpace, $parameterName, $userId)
    {
        ContextParameterManager::setUserValue($nameSpace, $parameterName, null, $userId);
    }
}
