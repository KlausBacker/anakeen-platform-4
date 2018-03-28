<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Router\Exception;

/**
 * Class DocumentViewDelete
 *
 * @note Used by route : DELETE /api/v2/documents/{docid}/views/{view}
 * @package Anakeen\Routes\Ui
 */
class DocumentViewDelete extends DocumentView
{
    public function doRequest(&$messages = [])
    {

        $messages = [];

        $document = $this->getDocument($this->documentId);
        $err = $document->control("delete");
        if ($err) {
            $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }

        $err = $document->delete();
        if ($err) {
            $exception = new Exception("ROUTES0111", $this->documentId, $err);
            throw $exception;
        }


        return parent::doRequest($messages);
    }

    protected function getEtagInfo($docid)
    {
        return null;
    }
}
