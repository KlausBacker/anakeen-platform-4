<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Settings;
use Anakeen\Router\URLUtils;
use Anakeen\Router\Exception;
use Anakeen\SmartElementManager;

/**
 * Class DocumentList
 *
 * List all visible documents
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/revisions/
 * @note    Used by route : GET /api/v2/families/{family}/documents/{docid}/revisions/
 * @package Anakeen\Routes\Core
 */
class RevisionList extends DocumentList
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $_document = null;
    protected $rootLevel = "documents";
    protected $documentId;
    protected $orderBy = "revision:desc";


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);

        $this->documentId = $args["docid"];

        $this->_document = SmartElementManager::getDocument($this->documentId);
        if (!$this->_document) {
            $exception = new Exception("CRUD0200", $this->documentId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        if ($this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0219", $this->documentId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setURI(URLUtils::generateURL(sprintf(
                "%strash/%d/revisions/",
                Settings::ApiV2,
                $this->_document->initid
            )));
            throw $exception;
        }
    }


    public function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf(
            "%s%s/%s/revisions/",
            Settings::ApiV2,
            $this->rootLevel,
            $this->documentId
        ));

        $data["revisions"] = $data["documents"];
        unset($data["documents"]);

        return $data;
    }

    protected function prepareDocumentFormatter($documentList)
    {
        $documentFormatter = parent::prepareDocumentFormatter($documentList);
        $documentFormatter->addProperty("revision");
        $documentFormatter->addProperty("status");
        return $documentFormatter;
    }

    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $this->_searchDoc->addFilter("initid = %d", $this->_document->initid);

        $this->_searchDoc->setOrder($this->orderBy);
        $this->_searchDoc->latest = false;
    }

    /**
     * Get the restricted attributes
     *
     * @throws Exception
     * @return array
     */
    protected function getAttributeFields()
    {
        $prefix = self::GET_ATTRIBUTE;
        $fields = $this->getFields();
        if ($this->hasFields(self::GET_ATTRIBUTE) || $this->hasFields(self::GET_ATTRIBUTES)) {
            return \Anakeen\Routes\Core\Lib\DocumentUtils::getAttributesFields($this->_document, $prefix, $fields);
        }
        return array();
    }

    protected function extractOrderBy()
    {
        return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($this->orderBy, $this->_document);
    }
}
