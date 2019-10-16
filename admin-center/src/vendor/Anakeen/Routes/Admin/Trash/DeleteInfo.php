<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentData;
use Anakeen\Core\SEManager;
use DocRel;

/**
 *
 * Get info about element before deletion
 *
 * @note    Used by route : GET /api/v2/trash/{docid}
 */

 
class DeleteInfo
{
    protected $element;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->documentId = $args["docid"];
        $this->element = SEManager::getDocument($this->documentId);
        
        if (!$this->element) {
            $exception = new Exception(sprintf("Element \"%s\" not exist", $this->$args["docid"]));
            throw $exception;
        }

        $relations=new DocRel();
        $relations->sinitid = $this->element->initid;

        $relatedElements= $relations->getIRelations();

        $nbRelatedElements= count($relatedElements);

        $data = $nbRelatedElements;

        return ApiV2Response::withData($response, $data);
    }
}
