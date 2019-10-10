<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;

class SmartElementUpdate
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
            if (empty($smartElement)) {
                $exception = new Exception("ANKTEST001", $args['seId']);
                $exception->setHttpStatus("500", "Cannot update Smart Element");
                $exception->setUserMessage(err);
                throw $exception;
            }
            $newValues = $request->getParsedBody();
            if (isset($newValues)) {
                foreach ($newValues as $aid => $value) {
                    try {
                        if ($value === null or $value === '') {
                            $smartElement->setAttributeValue($aid, null);
                        } else {
                            $smartElement->setAttributeValue($aid, $value);
                        }
                        $error = $smartElement->store();
                        if (!empty($error)) {
                            $exception = new Exception("ANKTEST003", $smartElement->id, $error);
                            $exception->setHttpStatus("500", "Unable to update the smart element");
                            throw $exception;
                        }
                    } catch (SmartFieldValueException $e) {
                        $exception = new Exception("ANKTEST002", $smartElement->id, $aid, $e->getDcpMessage());
                        $exception->setHttpStatus("500", "Unable to update the smart element");
                        $info = array(
                            "id" => $aid,
                            "index" => $e->index,
                            "err" => $e->originalError ? $e->originalError : $e->getDcpMessage()
                        );
                        $exception->setData($info);
                        throw $exception;
                    }
                }
            }
            $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($smartElement);
            $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
            return ApiV2Response::withData($response, $smartElementData->getDocumentData());
        } else {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
    }
}
