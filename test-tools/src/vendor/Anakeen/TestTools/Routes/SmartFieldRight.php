<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Core\SmartStructure\FieldAccessManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartElement;

class SmartFieldRight
{
    /** @var SmartElement */
    protected $smartElement;
    protected $smartfieldId;
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


        return ApiV2Response::withData($response, $this->testSmartFieldRight());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $seId = $args['seId'] ?? null;
        if (empty($seId)) {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }

        $this->smartElement = SEManager::getDocument($seId);
        if (empty($this->smartElement)) {
            $exception = new Exception("ANKTEST001", $seId);
            $exception->setHttpStatus("500", "Cannot get smart element");
            throw $exception;
        }

        $this->smartfieldId = $args['smartfield'] ?? null;
        if (empty($this->smartfieldId)) {
            $exception = new Exception("ANKTEST004", 'smartfield');
            $exception->setHttpStatus("400", "smartfield identifier is required");
            throw $exception;
        }

        $this->acl = $args['acl'] ?? null;
        if (empty($this->acl)) {
            $exception = new Exception("ANKTEST004", 'acl');
            $exception->setHttpStatus("400", "acl identifier is required");
            throw $exception;
        }

        if ($this->acl !== "none" && $this->acl !== "read" && $this->acl !== "write") {
            $exception = new Exception("ANKTEST010", 'acl');
            $exception->setHttpStatus("500", "acl must be none, read, write");
            throw $exception;
        }

        return "Access Field granted";
    }

    protected function testSmartFieldRight()
    {
        $smartfieldAttr = $this->smartElement->getAttribute(strtolower($this->smartfieldId));
        if (empty($smartfieldAttr)) {
            $exception = new Exception("ANKTEST004", 'smartfieldAttr');
            $exception->setHttpStatus("400", "smartfieldAttr not allowed");
            throw $exception;
        }

        switch ($this->acl) {
            case 'none':
                if (FieldAccessManager::hasReadAccess($this->smartElement, $smartfieldAttr) === true) {
                    $exception = new Exception("ANKTEST011", 'none');
                    $exception->setHttpStatus("400", "smartfieldAttr not allowed");
                    throw $exception;
                }
                break;
            case 'read':
                if (FieldAccessManager::hasReadAccess($this->smartElement, $smartfieldAttr) === false) {
                    $exception = new Exception("ANKTEST011", 'read');
                    $exception->setHttpStatus("400", "smartfieldAttr not allowed");
                    throw $exception;
                }
                break;
            case 'write':
                if (FieldAccessManager::hasWriteAccess($this->smartElement, $smartfieldAttr) === false) {
                    $exception = new Exception("ANKTEST011", 'write');
                    $exception->setHttpStatus("400", "smartfieldAttr not allowed");
                    $exception->setData([
                        "field" => $smartfieldAttr->id,
                        "user" => ContextManager::getCurrentUser()->login]);
                    throw $exception;
                }
                break;
        }
    }


}
