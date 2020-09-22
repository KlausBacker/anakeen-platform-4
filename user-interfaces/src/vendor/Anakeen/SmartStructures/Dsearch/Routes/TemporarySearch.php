<?php

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Core\Account;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\FamilyDocumentCreation;
use SmartStructure\Search;

/**
 * Class Attributes
 * @note    Used by route : GET /api/v2/smartstructures/dsearch/temporarySearch/{family}}
 * @package Anakeen\SmartStructures\Dsearch\Routes
 */
class TemporarySearch extends FamilyDocumentCreation
{
    public function create(\Slim\Http\request $request, SmartStructure $family, &$messages)
    {
        $uid = ContextManager::getCurrentUser()->id;
        try {
            $this->_document = SEManager::createTemporaryDocument($this->_family->id);
            if (is_a(Search::familyName, $this->_document)) {
                $exception = new Exception("APIDM0205", $this->_family->name);
                $exception->setHttpStatus(400, "Not a search");
                throw $exception;
            };
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
        $this->_document->accessControl()->setProfil($this->_document->id);
        if ($uid >  Account::ADMIN_ID) {
            $this->_document->accessControl()->addControl($uid, "view");
            $this->_document->accessControl()->addControl($uid, "edit");
            $this->_document->accessControl()->addControl($uid, "execute");
            $this->_document->accessControl()->addControl($uid, "delete");
        }
        return $this->_document;
    }
}
