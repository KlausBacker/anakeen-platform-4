<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use Anakeen\SmartElementManager;

class SmartElementCreation
{
    /** @var SmartElement $smartElement */
    protected $smartElement;

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
    
        $this->initParameters($request, $args);

        $this->getSmartElementdata();

        return ApiV2Response::withData($response, $this->getSmartElementdata());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $structure = $args['structure'] ?? null;
        if (empty($structure)) {
            $exception = new Exception("ANKTEST004", 'structure');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
        if (!empty($structure)) {
            $this->smartElement = SmartElementManager::createDocument($structure);
            $requestData = $request->getParsedBody();
            if (isset($requestData['document']['attributes'])) {
                $newValues = $requestData['document']['attributes'];
                foreach ($newValues as $aid => $value) {
                    try {
                        if ($value === null or $value === '') {
                            $this->smartElement->setAttributeValue($aid, null);
                        } else {
                            $this->smartElement->setAttributeValue($aid, $value);
                        }
                    } catch (SmartFieldValueException $e) {
                        $exception = new Exception("ANKTEST003", $this->smartElement->id, $aid, $e->getDcpMessage());
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
            $error = $this->smartElement->store();
            if (!empty($error)) {
                $exception = new Exception("ANKTEST001", $this->smartElement->id, $error);
                $exception->setHttpStatus("500", "Unable to create the SmartElement");
                throw $exception;
            }
        } else {
            $exception = new Exception("ANKTEST002", $this->smartElement->id, $error);
            $exception->setHttpStatus("400", "Structure identifier is required");
            throw $exception;
        }
        if (isset($requestData['document']['options']['tag'])) {
            $this->smartElement->addATag('ank_test', $requestData['document']['options']['tag']);
        }
    }

    protected function getSmartElementdata()
    {
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return $smartElementData->getDocumentData();
    }
}
