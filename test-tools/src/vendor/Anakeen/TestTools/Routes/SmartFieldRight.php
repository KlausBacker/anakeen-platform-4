<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;
use Anakeen\SmartElement;

class SmartFieldRight
{
    /** @var SmartElement */
    protected $smartElement;
    protected $acl;

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
        $seId = $args['seId'] ?? null;
        if (empty($seId)) {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }

        $this->smartElement=SEManager::getDocument($seId);
        if (empty($this->smartElement)) {
            $exception = new Exception("ANKTEST001", $seId);
            $exception->setHttpStatus("500", "Cannot get smart element");
            $exception->setUserMessage(err);
            throw $exception;
        }

        $this->acl = $args['acl'] ?? null;
        $err = $this->smartElement->control($this->acl);
        if (!empty($err)) {
            $exception = new Exception("ANKTEST003", $this->smartElement->id, $err);
            $exception->setHttpStatus("403", "Access forbidden");
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
