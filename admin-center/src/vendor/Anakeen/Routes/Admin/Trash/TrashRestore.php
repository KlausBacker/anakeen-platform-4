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

 
class TrashRestore extends DocumentData
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        // $mb = microtime(true);
        $this->documentId = $args["docid"];
        // $this->setDocument($this->documentId);
        $this->_document = SEManager::getDocument($this->documentId);
        
        if (!$this->_document) {
            $exception = new Exception(sprintf("Element \"%s\" not exist", $this->$args["docid"]));
            throw $exception;
        }
        
        $this->_document->disableAccessControl(true);
        $err = $this->_document->undelete();
        if ($err) {
            throw new \Anakeen\Ui\Exception("Unable to restore $err");
        }

        // $this->_document->addHistoryEntry(___("Definitively deleted by API", "ank"), \DocHisto::NOTICE, "DELETE");

        $data = $this->getDocumentData();
        // $data["duration"] = sprintf("%.04f", microtime(true) - $mb);
        return ApiV2Response::withData($response, $data);
    }
}
