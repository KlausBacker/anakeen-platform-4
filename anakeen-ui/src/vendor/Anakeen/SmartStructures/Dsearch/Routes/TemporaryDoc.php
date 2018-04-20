<?php

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Core\DocManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\FamilyDocumentCreation;

/**
 * Class Attributes
 * @note    Used by route : GET /api/v2/smartstructures/dsearch/temporaryDoc/{family}}
 * @package Anakeen\SmartStructures\Dsearch\Routes
 */
class TemporaryDoc extends FamilyDocumentCreation
{
    public function create(\Slim\Http\request $request, SmartStructure $family, &$messages)
    {
        try {
            $this->_document = DocManager::createTemporaryDocument($this->_family->id);
        } catch (\Anakeen\Core\DocManager\Exception $exception) {
            if ($exception->getDcpCode() === "APIDM0003") {
                $exception = new Exception("API0204", $this->_family->name);
                $exception->setHttpStatus(403, "Forbidden");
                throw $exception;
            } else {
                throw $exception;
            }
        }


        $this->updateDocument($request, $messages);
        return $this->_document;
    }
}
