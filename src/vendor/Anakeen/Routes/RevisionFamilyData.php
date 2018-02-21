<?php

namespace Anakeen\Routes\Core;

use Dcp\Core\DocManager;
use Anakeen\Router\Exception;

/**
 * Class FamilyData
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/revisions/{revisionNumber}
 * @package Anakeen\Routes\Core
 */
class RevisionFamilyData extends RevisionData
{
    /**
     * @var \DocFam
     */
    protected $_family = null;
    /**
     * Get ressource
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->documentId=$args["docid"];
        $this->revisionNumber = $args["revisionNumber"];
        $famName=$args["family"];

        $this->_family = DocManager::getFamily($famName);
        if (!$this->_family) {
            $exception = new Exception("ROUTES0105", $famName);
            $exception->setHttpStatus("404", "Family not found");
            $exception->setUserMessage(sprintf(___("Family \"%s\" not found", "ank"), $famName));
            throw $exception;
        }

        $this->setDocument($this->documentId);
        if (!is_a($this->_document, sprintf("\\Dcp\\Family\\%s", $this->_family->name))) {
            $exception = new Exception("ROUTES0104", $this->documentId, $this->_family->name);
            $exception->setHttpStatus("404", "Document is not a document of the family " . $this->_family->name);
            throw $exception;
        }

        return parent::__invoke($request, $response, $args);
    }
}
