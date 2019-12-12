<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use Anakeen\SmartElementManager;
use SmartStructure\Wdoc;

class Transition
{
    /** @var SmartElement */
    protected $smartElement;
    /** @var Wdoc */
    protected $workflow;
    protected $transition;
    protected $askValues;

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

        $this->doTransition();

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
            $exception = new Exception("ANKTEST001", $docid);
            $exception->setHttpStatus("500", "Cannot update Smart Element");
            throw $exception;
        }

        $this->transition = $args['transition'] ?? null;
        if (empty($this->transition)) {
            $exception = new Exception("ANKTEST004", 'transition');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
                
        $wid = $this->smartElement->wid;
        if ($wid === 0) {
            $exception = new Exception("ANKTEST003", $this->smartElement->id);
            $exception->setHttpStatus("500", "There is no workflow");
            throw $exception;
        }

        $this->workflow = SmartElementManager::getDocument($wid);
        if (empty($this->workflow)) {
            $exception = new Exception("ANKTEST003", $this->smartElement->id);
            $exception->setHttpStatus("404", "Cannot find workflow");
            throw $exception;
        }

        $this->askValues = $request->getParsedBody();
    }

    protected function doTransition()
    {
        $nextState = '';
        foreach ($this->workflow->cycle as $wTransition) {
            if (($wTransition["e1"] === $this->smartElement->state)
                && ($wTransition["t"] === $this->transition)) {
                $nextState = $wTransition["e2"];
            }
        }
        if (empty($nextState)) {
            $exception = new Exception("ANKTEST003", $this->smartElement->id);
            $exception->setHttpStatus("400", sprintf("No target state found from %s with transition %s", $this->smartElement->state, $this->transition));
            throw $exception;
        }
        if (!empty($this->askValues) && is_array($this->askValues)) {
            foreach ($this->askValues as $askAttrId => $askValue) {
                $this->workflow->setAskValue($askAttrId, $askValue);
            }
        }
        $error = $this->smartElement->setState($nextState);
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
