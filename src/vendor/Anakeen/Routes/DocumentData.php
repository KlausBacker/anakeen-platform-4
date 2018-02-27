<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Dcp\Core\DbManager;
use Dcp\Core\DocManager;
use Dcp\Core\Settings;
use Dcp\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Dcp\Routes\Document;

/**
 * Class DocumentData
 *
 * @note    Used by route : GET /api/v2/documents/{docid}
 * @package Anakeen\Routes\Core
 */
class DocumentData
{
    const GET_PROPERTIES = "document.properties";
    const GET_PROPERTY = "document.properties.";
    const GET_ATTRIBUTES = "document.attributes";
    const GET_ATTRIBUTE = "document.attributes.";
    const GET_STRUCTURE = "family.structure";
    /**
     * @var \Doc document instance
     */
    protected $_document = null;

    protected $defaultFields = null;
    protected $returnFields = null;
    protected $valueRender = array();
    protected $propRender = array();
    /**
     * @var DocumentApiData
     */
    protected $data;
    /**
     * @var \Slim\Http\request
     */
    protected $request;
    /**
     * @var DocumentDataFormatter
     */
    protected $documentFormater = null;
    /**
     * @var int document icon width in px
     */
    public $iconSize = 32;
    protected $documentId;

    /**
     * DocumentData constructor.
     *
     */
    public function __construct()
    {
        $this->defaultFields = self::GET_PROPERTIES . "," . self::GET_ATTRIBUTES;
    }

    /**
     * Get ressource
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\Response
     * @throws Exception
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $mb = microtime(true);
        $this->request = $request;
        $this->documentId = $args["docid"];
        $this->returnFields = null;

        if (!$this->checkId($this->documentId, $initid)) {
            // Redirect to other url
            $document = DocManager::getDocument($initid, false);
            $location = DocumentUtils::getURI($document);
            return $response->withStatus(307)
                ->withHeader("location", $location);
        }

        $this->setDocument($this->documentId);
        $etag = $this->getDocumentEtag($this->_document->id);
        $response = ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }

        $err = $this->_document->control("view");
        if (!$err) {
            if ($this->_document->isConfidential()) {
                $err = "Confidential document";
            }
        } else {
            $err = "Access not granted";
        }
        if ($err) {
            $exception = new Exception("ROUTES0101", $this->documentId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            $exception->setUserMessage(___("Document access not granted", "ank"));
            throw $exception;
        }
        if ($this->_document->mid == 0) {
            $this->_document->applyMask(\Doc::USEMASKCVVIEW);
        }
        $data = $this->getDocumentData();
        $data["duration"] = sprintf("%.04f", microtime(true) - $mb);
        return ApiV2Response::withData($response, $data);
    }

    /**
     * Find the current document and set it in the internal options
     *
     * @param $ressourceId string|int identifier of the document
     *
     * @throws Exception
     */
    protected function setDocument($ressourceId)
    {
        $this->_document = DocManager::getDocument($ressourceId);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $ressourceId);
            $exception->setHttpStatus("404", "Document not found");
            $exception->setUserMessage(sprintf(___("Document \"%s\" not found", "ank"), $ressourceId));
            throw $exception;
        }
        if ($this->_document->doctype === "Z") {
            $exception = new Exception("ROUTES0102", $ressourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setUserMessage(sprintf(___("Document \"%s\" is deleted", "ank"), $ressourceId));
            $location = URLUtils::generateUrl(sprintf("%s/trash/%d", Settings::ApiV2, $this->_document->initid));
            $exception->setURI($location);
            throw $exception;
        }

        DocManager::cache()->addDocument($this->_document);
    }


    /**
     * Get data from document object
     * No access control are done
     *
     * @param \Doc $document Document
     *
     * @throws Exception
     * @return mixed
     */
    public function getInternal(\Doc $document)
    {
        $this->_document = $document;
        return $this->getDocumentData();
    }


    /**
     * Get the restrict fields value
     *
     * The restrict fields is used for restrict the return of the get request
     *
     * @return array|null
     */
    protected function getFields()
    {
        if ($this->returnFields === null) {
            if ($this->request) {
                $fields = $this->request->getQueryParam("fields");
                if (empty($fields)) {
                    $fields = $this->defaultFields;
                }
                if ($fields) {
                    $this->returnFields = array_map("trim", explode(",", $fields));
                } else {
                    $this->returnFields = array();
                }
            } else {
                return array_map("trim", explode(",", $this->defaultFields));
            }
        }
        return $this->returnFields;
    }



    protected function getDocumentApiData()
    {
        return new DocumentApiData($this->_document);
    }


    /**
     * Get document data
     *
     * @throws Exception
     * @return array
     */
    protected function getDocumentData()
    {
        $this->data = $this->getDocumentApiData();
        $this->data->setFields($this->getFields());
        return $this->data->getDocumentData();
    }


    /**
     * Compute etag from a document id
     *
     * @param int $id
     *
     * @return string
     * @throws \Dcp\Db\Exception
     */
    protected static function getDocumentEtag($id)
    {
        $result = array();
        $sql = sprintf("select id, revdate, views from docread where id = %d", $id);

        DbManager::query($sql, $result, false, true);
        $user = \Dcp\Core\ContextManager::getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        // Necessary only when use family.structure
        $result[] = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return join(" ", $result);
    }

    /**
     * Check is the ID is canonical and redirect if not
     *
     * @param $identifier
     * @param $initid
     *
     * @return bool
     * @throws \Dcp\Core\Exception
     */
    protected function checkId($identifier, &$initid)
    {
        if (is_numeric($identifier)) {
            $identifier = (int)$identifier;
            $initid = DocManager::getInitIdFromIdOrName($identifier);

            if ($initid !== 0 && $initid != $identifier) {
                return false;
            }
        }
        return true;
    }
}
