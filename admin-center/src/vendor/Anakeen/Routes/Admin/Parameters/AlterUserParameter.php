<?php

namespace Anakeen\Routes\Admin\Parameters;


use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\ContextParameterManager;

class AlterUserParameter
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @throws \Dcp\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // Alter parameter for user
        $userLogin = $args['user'];
        $userId = AccountManager::getIdFromLogin($userLogin);

        // Namespace and parameter name
        $nameSpace = $args['namespace'];
        $parameterName = $args['parameter_name'];

        // New value of parameter
        $newValue = $request->getParam('value');

        // TODO Verify value before change

        // Modify parameter value
        ContextParameterManager::setUserValue($nameSpace, $parameterName, $newValue, $userId);

        return $response->withJson(['value' => $newValue]);
    }
}