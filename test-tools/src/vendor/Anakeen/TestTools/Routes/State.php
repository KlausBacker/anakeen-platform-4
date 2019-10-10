<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\SmartElementManager;
use Anakeen\WorkflowSetState;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\WorkflowState;

class State
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        if (!empty($args['docid'])) {
            $smartElement = SmartElementManager::getDocument($args['docid']);
            if (empty($smartElement)) {
                $exception = new Exception("ANKTEST001", $args['docid']);
                $exception->setHttpStatus("500", "Cannot update Smart Element");
                $exception->setUserMessage(err);
                throw $exception;
            }
            $error = $smartElement->setState($args['state']);
            if (!empty($error)) {
                $exception = new Exception("ANKTEST003", $smartElement->id, $error);
                $exception->setHttpStatus("500", "Unable to set the smart element state");
                throw $exception;
            }
        } else {
            $exception = new Exception("ANKTEST004", 'docid');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return ApiV2Response::withData($response, $smartElementData->getDocumentData());
    }
}
