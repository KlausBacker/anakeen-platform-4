<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentData;
use Anakeen\Core\SEManager;

/**
 * Class DeleteDocumentFromTrash
 *
 * Delete document from the trash
 *
 * @note    Used by route : DELETE /api/v2/trash/{docid}
 * @package Anakeen\Routes\Admin\Trash;
 */

 
class TrashDelete extends DocumentData
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->request = $request;
        $this->documentId = $args["docid"];
        $this->_document = SEManager::getDocument($this->documentId);
        
        if (!$this->_document) {
            $exception = new Exception(sprintf("Element \"%s\" not exist", $this->$args["docid"]));
            throw $exception;
        }

        $this->_document->disableAccessControl(true);
        $err = $this->_document->delete(true, false);
        if ($err) {
            $exception = new Exception("DELETE_TRASH", $this->_document->getTitle(), $err);
            throw $exception;
        }

        $data = $this->getDocumentData();
        return ApiV2Response::withData($response, $data);
    }
}
