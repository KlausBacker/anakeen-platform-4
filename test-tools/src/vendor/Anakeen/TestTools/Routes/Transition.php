<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\SmartElementManager;
use Anakeen\SmartStructures\Wdoc;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class Transition
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
                throw $exception;
            }
            if (!empty($args['transition'])) {
                $wid = $smartElement->wid;
                if ($wid !== 0) {
                    $workflow = SmartElementManager::getDocument($wid);
                    if (empty($workflow)) {
                        $exception = new Exception("ANKTEST003", $smartElement->id);
                        $exception->setHttpStatus("404", "Cannot find workflow");
                        throw $exception;
                    }
                    $nextState = '';
                    foreach ($workflow->cycle as $wTransition) {
                        if (($wTransition["e1"] === $smartElement->state) && ($wTransition["t"] === $args["transition"])) {
                            $nextState = $wTransition["e2"];
                        }
                    }
                    if (!empty($nextState)) {
                        $error = $smartElement->setState($nextState);
                        if (!empty($error)) {
                            $exception = new Exception("ANKTEST003", $smartElement->id, $error);
                            $exception->setHttpStatus("500", "Unable to set the smart element state");
                            throw $exception;
                        }
                        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($smartElement);
                        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
                        return ApiV2Response::withData($response, $smartElementData->getDocumentData());
                    }
                } else {
                    $exception = new Exception("ANKTEST003", $smartElement->id);
                    $exception->setHttpStatus("500", "There is no workflow");
                    throw $exception;
                }
            }
        } else {
            $exception = new Exception("ANKTEST004", 'docid');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
    }
}
