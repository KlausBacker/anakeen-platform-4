<?php

namespace Anakeen\Routes\Core;

use Dcp\Core\DocManager;
use Anakeen\Router\URLUtils;
use Dcp\Core\Settings;
use Anakeen\Router\Exception;

/**
 * Class FamilyData
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/revisions/{revisionNumber}
 * @package Anakeen\Routes\Core
 */
class RevisionData extends DocumentData
{
    protected $revisionNumber;

    protected function checkId($identifier, &$initid)
    {
        $checkId = parent::checkId($identifier, $initid);
        if (!$checkId) {
            $initid = $this->documentId;
        }
        return true;
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->revisionNumber = $args["revisionNumber"];
        return parent::__invoke($request, $response, $args);
    }

    /**
     * Find the current document and set it in the internal options
     *
     * @param $ressourceId string|int identifier of the document
     *
     * @throws Exception
     */
    protected function setDocument($ressourceId)
    {
        $revisedId = DocManager::getRevisedDocumentId($this->documentId, $this->revisionNumber);
        $this->_document = DocManager::getDocument($revisedId, false);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $ressourceId);
            $exception->setHttpStatus("404", "Document not found");
            $exception->setUserMessage(sprintf(___("Document \"%s\" not found", "ank"), $ressourceId));
            throw $exception;
        }
        if ($this->_document->doctype === "Z") {
            $exception = new Exception("ROUTES0102", $ressourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $location = URLUtils::generateUrl(sprintf("%s/trash/%d", Settings::ApiV2, $this->_document->initid));
            $exception->setURI($location);
            throw $exception;
        }

        DocManager::cache()->addDocument($this->_document);
    }
}
