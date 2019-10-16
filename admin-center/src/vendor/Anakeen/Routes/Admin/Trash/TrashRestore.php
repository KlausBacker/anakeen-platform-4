<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentData;
use Anakeen\Core\SEManager;

/**
 * Class TrashRestore
 *
 * Restore element from the trash to the origin place
 *
 * @note    Used by route : PUT /api/v2/trash/{docid}
 * @package Anakeen\Routes\Admin\Trash;
 */

 
class TrashRestore extends DocumentData
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->documentId = $args["docid"];
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

        $data = $this->getDocumentData();
        return ApiV2Response::withData($response, $data);
    }
}
