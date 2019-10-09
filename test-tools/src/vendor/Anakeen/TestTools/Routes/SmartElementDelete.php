<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\SmartElementManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class SmartElementDelete
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
        if (!empty($args['seId'])) {
            $smartElement = SmartElementManager::getDocument($args['seId']);
            $error = $smartElement->delete();
            if (!empty($error)) {
                $exception = new Exception("ANKTEST003", $smartElement->id, $error);
                $exception->setHttpStatus("500", "Unable to delete the smart element");
                throw $exception;
            }
            $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($smartElement);
            $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
            return ApiV2Response::withData($response, $smartElementData->getDocumentData());
        } else {
            $exception = new Exception("ANKTEST005", $smartElement->id);
            $exception->setHttpStatus("400", "smart element doesn't exist");
            throw $exception;
        }
    }
}
