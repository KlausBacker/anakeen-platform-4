<?php

namespace Anakeen\Routes\Core;

use Dcp\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class DocumentDeleteData
 *
 * Put document to the trash
 *
 * @note    Used by route : GET /api/v2/documents/{docid}
 * @package Anakeen\Routes\Core
 */
class DocumentDeleteData extends DocumentData
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $mb = microtime(true);
        $this->request = $request;
        $this->documentId = $args["docid"];
        $this->setDocument($this->documentId);

        $err = $this->_document->control("delete");
        if ($err) {
            $exception = new Exception("ROUTES0110", $this->documentId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }

        $err = $this->_document->delete();
        if ($err) {
            $exception = new Exception("ROUTES0111", $this->_document->getTitle(), $err);
            throw $exception;
        }
        $this->_document->addHistoryEntry(___("Deleted by API", "ank"), \DocHisto::NOTICE, "DELETE");

        $data = $this->getDocumentData();
        $data["duration"] = sprintf("%.04f", microtime(true) - $mb);
        return ApiV2Response::withData($response, $data);
    }
}
