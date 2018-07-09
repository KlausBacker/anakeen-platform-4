<?php

namespace Anakeen\Routes\Admin\Parameters;


use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Dcp\Exception;

class RestoreUserParameterSystemValue
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // User
        $userLogin = $args['user'];
        $userId = AccountManager::getIdFromLogin($userLogin);

        // Parameter
        $nameSpace = $args['name_space'];
        $parameterName = $args['parameter_name'];

        try {
            ContextParameterManager::setUserValue($nameSpace, $parameterName, null, $userId);
        } catch (Exception $e) {
            return $response->withStatus(500, 'Error during restoration');
        }

        return $response->withJson(['success' => 'true']);
    }
}