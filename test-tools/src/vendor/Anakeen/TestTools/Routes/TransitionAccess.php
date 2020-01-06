<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use SmartStructure\Wdoc;

class TransitionAccess
{
    /** @var SmartElement */
    protected $smartElement;
    /** @var Wdoc */
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


        return ApiV2Response::withData($response, $this->checkTransition());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $docid = $args['docid'] ?? null;
        if (empty($docid)) {
            $exception = new Exception("ANKTEST004", 'docid');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }

        $this->smartElement = SEManager::getDocument($docid);
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

        $this->workflow = SEManager::getDocument($wid);
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
        if (!empty($err)) {
            throw new Exception("ANKTEST012", $err);
        }
        return "Access granted";
    }
}
