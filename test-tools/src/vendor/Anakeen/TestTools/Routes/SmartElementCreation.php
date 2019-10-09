<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\SmartElementManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class SmartElementCreation
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
        if (!empty($args['structure'])) {
            $smartElement = SmartElementManager::createDocument($args['structure']);
            $requestData = $request->getParsedBody();
            if (isset($requestData['document']['attributes'])) {
                $newValues = $requestData['document']['attributes'];
                foreach ($newValues as $aid => $value) {
                    try {
                        if ($value === null or $value === '') {
                            $smartElement->setAttributeValue($aid, null);
                        } else {
                            $smartElement->setAttributeValue($aid, $value);
                        }
                    } catch (SmartFieldValueException $e) {
                        $exception = new Exception("ANKTEST003", $smartElement->id, $aid, $e->getDcpMessage());
                        $exception->setHttpStatus("500", "Unable to modify the smart element");
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
            $error = $smartElement->store();
            if (!empty($error)) {
                $exception = new Exception("ANKTEST001", $smartElement->id, $error);
                $exception->setHttpStatus("500", "Unable to create the SmartElement");
                throw $exception;
            }
        } else {
            $exception = new Exception("ANKTEST002", $smartElement->id, $error);
            $exception->setHttpStatus("400", "Structure identifier is required");
            throw $exception;
        }
        if (isset($requestData['document']['options']['tag'])) {
            $smartElement->addATag('ank_test', $requestData['document']['options']['tag']);
        }
        $response = $response->withStatus(201);
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return ApiV2Response::withData($response, $smartElementData->getDocumentData());
    }
}
