<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\SmartStructure\SmartFieldValueException;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use Anakeen\SmartElementManager;

class SmartElementCreation
{
    /** @var SmartElement $smartElement */
    protected $smartElement;
    protected $structure;
    protected $requestData;

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

        $this->createSmartElement();

        return ApiV2Response::withData($response, $this->getSmartElementdata());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->structure = $args['structure'] ?? null;
        if (empty($this->structure)) {
            $exception = new Exception("ANKTEST004", 'structure');
            $exception->setHttpStatus("400", "structure identifier is required");
            throw $exception;
        }

        $this->requestData = $request->getParsedBody();
    }

    protected function createSmartElement()
    {
        $this->smartElement = SmartElementManager::createDocument($this->structure);
        if (empty($this->smartElement)) {
            $exception = new Exception("ANKTEST013", $this->structure);
            $exception->setHttpStatus("500", "can not create smart element");
            throw $exception;
        }
  
        if (isset($this->requestData['document']['attributes'])) {
            $newValues = $this->requestData['document']['attributes'];
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
            $exception = new Exception("ANKTEST014", $this->smartElement->id, $error);
            $exception->setHttpStatus("500", "Unable to store the SmartElement");
            throw $exception;
        }
        
        if (isset($this->requestData['document']['options']['tag'])) {
            $this->smartElement->addATag('ank_test', $this->requestData['document']['options']['tag']);
        }
    }

    protected function getSmartElementdata()
    {
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return $smartElementData->getDocumentData();
    }
}
