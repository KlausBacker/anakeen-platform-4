<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\DocManager;
use Anakeen\Router\Exception;

/**
 * Class DocumentDeletedData
 *
 * Get data from deleted document
 * @note    Used by route : GET /api/v2/trash/{docid}
 * @package Anakeen\Routes\Core
 */
class DocumentDeletedData extends DocumentData
{
    protected function setDocument($ressourceId)
    {
        $this->_document = DocManager::getDocument($ressourceId, true);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $ressourceId);
            $exception->setHttpStatus("404", "Document not found");
            $exception->setUserMessage(sprintf(___("Deleted document \"%s\" not found", "ank"), $ressourceId));
            throw $exception;
        }
        if ($this->_document->doctype !== "Z") {
            $e = new Exception("ROUTES0112", $ressourceId);
            $e->setHttpStatus("404", "Document not in the trash");
            $e->setUserMessage(sprintf(___("Document \"%s\" is not in the trash", "ank"), $ressourceId));
            throw $e;
        }

        DocManager::cache()->addDocument($this->_document);
    }
}
