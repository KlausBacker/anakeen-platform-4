<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use Anakeen\SmartElementManager;

class State
{
    /** @var SmartElement $smartElement */
    protected $smartElement;
    protected $state;
    
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

        $this->setSmartElementState();

        return ApiV2Response::withData($response, $this->getSmartElementdata());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $docid = $args['docid'] ?? null;
        if (empty($docid)) {
            $exception = new Exception("ANKTEST004", 'docid');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
    
        $this->smartElement = SmartElementManager::getDocument($docid);
        if (empty($this->smartElement)) {
            $exception = new Exception("ANKTEST001", $args['docid']);
            $exception->setHttpStatus("500", "Cannot update Smart Element");
            $exception->setUserMessage(err);
            throw $exception;
        }

        $this->state = $args['state'] ?? null;
    }

    protected function setSmartElementState()
    {
        $error = $this->smartElement->setState($this->state);
        if (!empty($error)) {
            $exception = new Exception("ANKTEST003", $this->smartElement->id, $error);
            $exception->setHttpStatus("500", "Unable to set the smart element state");
            throw $exception;
        }
    }

    protected function getSmartElementdata()
    {
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return $smartElementData->getDocumentData();
    }
}
