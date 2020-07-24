<?php


namespace Anakeen\Routes\TransformationEngine\Admin;


use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\TransformationEngine\Client;
use Anakeen\TransformationEngine\ClientException;
use Slim\Http\Request;
use Slim\Http\Response;

class CheckUnitTransformationFile
{
    public function __invoke(request $request, response $response, $args)
    {
        $clientTE = new Client(ContextManager::getParameterValue("TE", "TE_HOST"), ContextManager::getParameterValue("TE", "TE_PORT"));
        $tmpFile = tempnam(ContextManager::getTmpDir(), "test-unit-transformation");
        $tid = $args["task"];
        $err = $clientTE->getTransformation(
            $tid,
            $tmpFile
        );
        if ($err != '') {
            throw new ClientException(sprintf("getTransformation() returned with error: %s", $err));
        }
        return ApiV2Response::withFile($response, $tmpFile);
    }
}