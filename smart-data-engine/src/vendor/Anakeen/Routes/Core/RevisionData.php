<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Router\URLUtils;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;
use Anakeen\SmartElementManager;

/**
 * Class FamilyData
 *
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/revisions/{revisionNumber}
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/revisions/{revisionNumber}
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

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $this->revisionNumber = $args["revisionNumber"];
    }

    protected function doRequest(&$messages = [])
    {
        $info = parent::doRequest($messages);

        $info["revision"] = $info["document"];
        unset($info["document"]);
        return $info;
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
        $revisedId = SEManager::getRevisedDocumentId($this->documentId, $this->revisionNumber);
        $this->_document = SmartElementManager::getDocument($revisedId, false);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $ressourceId);
            $exception->setHttpStatus("404", "Document not found");
            $exception->setUserMessage(sprintf(___("Document \"%s\" not found", "ank"), $ressourceId));
            throw $exception;
        }
        if (!$this->useTrash && $this->_document->doctype === "Z") {
            $exception = new Exception("ROUTES0102", $ressourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $location = URLUtils::generateUrl(sprintf("%s/trash/%d", Settings::ApiV2, $this->_document->initid));
            $exception->setURI($location);
            throw $exception;
        }

        SEManager::cache()->addDocument($this->_document);
    }
}
