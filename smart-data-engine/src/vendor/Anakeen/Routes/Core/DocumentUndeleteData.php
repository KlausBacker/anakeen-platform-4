<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

/**
 * Class DocumentUnDeleteData
 *
 * Undelete deleted document
 * Need to set {document:{properties:{status:alive}}} in request body to activate undeletion
 * @note    Used by route : PUT /api/v2/trash/{docid}
 * @package Anakeen\Routes\Core
 */
class DocumentUnDeleteData extends DocumentDeletedData
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->request=$request;
        $this->documentId=$args["docid"];
        $documentData=$request->getParsedBody();
        if (isset($documentData["document"]["properties"]["status"]) &&
            $documentData["document"]["properties"]["status"] === "alive") {
            $this->setDocument($this->documentId);


            $err = $this->_document->control("delete");
            if ($err) {
                $exception = new Exception("ROUTES0110", $this->documentId, $err);
                $exception->setHttpStatus("403", "Forbidden");
                throw $exception;
            }

            $err = $this->_document->undelete();
            $err .= $this->_document->store();
            if ($err) {
                $exception = new Exception("CRUD0505", $err);
                $exception->setHttpStatus("500", "Unable to restore the document");
                throw $exception;
            }

            $data = $this->getDocumentData();
            return ApiV2Response::withData($response, $data);
        }
        $exception = new Exception("ROUTES0113", $this->documentId);
        $exception->setHttpStatus("400", "You cannot update a document in the trash");
        throw $exception;
    }
}
