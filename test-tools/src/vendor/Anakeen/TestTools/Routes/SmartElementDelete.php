<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class SmartElementDelete
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

        $this->deleteSmartElement();

        $this->getSmartElementdata();

        return ApiV2Response::withData($response, $this->getSmartElementdata());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $seId = $args['seId'] ?? null;
        if (empty($seId)) {
            error_log(print_r(">>>>>>>>>>1", true));
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
    }

    protected function deleteSmartElement()
    {
        $error =  $this->smartElement->delete();
        if (!empty($error)) {
            error_log(print_r(">>>>>>>>>>3", true));
            $exception = new Exception("ANKTEST003", $this->smartElement->id, $error);
            $exception->setHttpStatus("500", "Unable to delete the smart element");
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
