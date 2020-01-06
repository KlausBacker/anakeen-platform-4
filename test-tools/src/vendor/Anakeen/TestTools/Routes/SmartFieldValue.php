<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartElement;
use Anakeen\SmartElementManager;

class SmartFieldValue
{
    /** @var SmartElement $smartElement */
    protected $smartElement;
    protected $values;

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

        $this->getSmartFieldValues();

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

        $this->values = $request->getParsedBody();
        if (null === $this->values) {
            $exception = new Exception("ANKTEST004", 'getValues');
            $exception->setHttpStatus("400", "problem with getParseBody");
            throw $exception;
        }
        //FIXME: check array
    }

    protected function getSmartFieldValues()
    {
        foreach ($this->values as $sFId => $sFvalue) {
            $realValue = $this->smartElement->getAttributeValue($sFId);

            if ($realValue !== $sFvalue) {
                $exception = new Exception("ANKTEST016", $realValue, $sFvalue);
                $exception->setHttpStatus("400", "Values not equal");
                throw $exception;
            }
        }
    }

    protected function getSmartElementdata()
    {
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->smartElement);
        $smartElementData->setFields(["document.attributes.all"]);
        return $smartElementData->getDocumentData();
    }
}
