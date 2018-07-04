<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\Internal\ContextParameterManager;

class AlterParameter
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Dcp\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // Parameter to change
        $parameterName = $args['parameter_name'];
        $nameSpace = $args['namespace'];

        // New value
        $newValue = $request->getParam('value');

        // Change value
        ContextParameterManager::setValue($nameSpace, $parameterName, $newValue);

        $responseData = ['namespace' => $nameSpace, 'parameter_name' => $parameterName, 'value' => $newValue];
        return $response->withJson($responseData);
    }
}