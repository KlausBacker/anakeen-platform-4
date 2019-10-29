<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use Anakeen\SmartElementManager;

class TransitionAccess
{
    /** @var SmartElement */
    protected $smartElement;
    /** @var SmartElement */
    protected $workflow;
    protected $transition;

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

        $this->checkTransition();

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
            $exception->setHttpStatus("500", sprintf("Cannot get Smart Element %s", $docid));
            throw $exception;
        }

        $this->transition = $args['transition'] ?? null;
        if (empty($this->transition)) {
            $exception = new Exception("ANKTEST004", 'transition');
            $exception->setHttpStatus("400", "transition identifier is required");
            throw $exception;
        }
                
        $wid = $this->smartElement->wid;
        if ($wid === 0) {
            $exception = new Exception("ANKTEST001", $this->smartElement->id);
            $exception->setHttpStatus("500", "There is no workflow");
            throw $exception;
        }

        $this->workflow = SmartElementManager::getDocument($wid);
        if (empty($this->workflow)) {
            $exception = new Exception("ANKTEST001", $this->smartElement->id);
            $exception->setHttpStatus("404", "Cannot find workflow");
            throw $exception;
        }

        $this->workflow->set($this->smartElement);
    }

    protected function checkTransition()
    {
        $err = $this->workflow->control($this->transition);
    }

    protected function getSmartElementdata()
    {
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return $smartElementData->getDocumentData();
    }
}
