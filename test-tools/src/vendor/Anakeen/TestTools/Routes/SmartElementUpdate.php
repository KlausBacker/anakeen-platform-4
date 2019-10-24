<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;

class SmartElementUpdate
{
    /** @var SmartElement $smartElement */
    protected $smartElement;
    protected $newValues;
    protected $dryRun;

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

        if($this->dryRun) {
            $this->initTransaction();
        }

        $this->updateValues();

        if($this->dryRun) {
            $this->rollbackTransaction();
        }

        return ApiV2Response::withData($response, $this->getSmartElementdata());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $seId = $args['seId'] ?? null;
        if (empty($seId)) {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }

        $this->smartElement = SmartElementManager::getDocument($seId);
        if (empty($this->smartElement)) {
            $exception = new Exception("ANKTEST001", $seId);
            $exception->setHttpStatus("500", "Cannot update Smart Element");
            throw $exception;
        }

        $this->newValues = $request->getParsedBody();
        if(null === $this->newValues) {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "problem with getParseBody");
            throw $exception;
        }

        $this->dryRun = $request->getQueryParams()["dry-run"] ?? false;
    }

    protected function updateValues()
    {
        foreach ($this->newValues as $aid => $value) {
            try {
                if ($value === null or $value === '') {
                    $this->smartElement->setAttributeValue($aid, null);
                } else {
                    $this->smartElement->setAttributeValue($aid, $value);
                }
                $error = $this->smartElement->store();
                if (!empty($error)) {
                    $exception = new Exception("ANKTEST003", $this->smartElement->id, $error);
                    $exception->setHttpStatus("500", "Unable to update the smart element");
                    throw $exception;
                }
            } catch (SmartFieldValueException $e) {
                $exception = new Exception("ANKTEST002", $this->smartElement->id, $aid, $e->getDcpMessage());
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

    protected function initTransaction()
    {
        $savepoint = DbManager::savePoint("seUpdate");
        if(!empty($savepoint)) {
            $exception = new Exception("ANKTEST001", $savepoint);
            $exception->setHttpStatus("500", "Cannot put the save point");
            $exception->setUserMessage(err);
            throw $exception;
        }
    }

    protected function rollbackTransaction()
    {    
        $rollback = DbManager::rollbackPoint('seUpdate');
        if(!empty($rollback)) {
            $exception = new Exception("ANKTEST001", $rollback);
            $exception->setHttpStatus("500", "Error rollback : save point is not define");
            $exception->setUserMessage(err);
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
