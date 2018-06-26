<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\Internal\ApplicationParameterManager;
use Dcp\ApplicationParameterManager\Exception;

class AlterParameter
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // Parameter to change
        $parameterName = $args['parameter_name'];
        $domainName = $args['domain_name'];

        // New value
        $newValue = $request->getParam('value');

        // Change value
        $err = ApplicationParameterManager::setParameterValue($domainName, $parameterName, $newValue);
        if ($err) {
            return $response->withStatus(500, $err)->write($err);
        }

        $responseData = ['parameter_name' => $parameterName, 'domain_name' => $domainName, 'value' => $newValue];
        return $response->withJson($responseData);
    }
}