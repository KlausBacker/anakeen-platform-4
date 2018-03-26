<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\DocManager;
use Dcp\Core\Settings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class DocumentUserTag
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/usertags/
 * @note    Used by route : GET /api/v2/families/{family}/documents/{docid}/usertags/
 * @package Anakeen\Routes\Core
 */
class DocumentUserTags
{
    protected $baseURL = "documents";
    /**
     * @var \Doc
     */
    protected $_document = null;
    /**
     * @var \DocFam
     */
    protected $_family = null;

    protected $slice = -1;

    protected $offset = 0;

    protected $revisionFilter = -1;
    protected $documentId;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $etag = $this->getEtagInfo();
        $response = ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["docid"];

        $this->setDocument($this->documentId);
        if (isset($args["family"])) {
            DocumentUtils::verifyFamily($args["family"], $this->_document);
        }
        $slice = $request->getQueryParam("slice");
        if ($slice !== null) {
            $this->setSlice($slice);
        }

        $offset = $request->getQueryParam("offset");
        if ($offset !== null) {
            $this->setOffset($offset);
        }
    }


    public function doRequest()
    {
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $this->documentId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }

        $info = array();

        $q = new \QueryDb($this->_document->dbaccess, "docUTag");
        $q->addQuery(sprintf("uid=%d", ContextManager::getCurrentUser()->id));
        $q->addQuery(sprintf("initid = %d", $this->_document->initid));
        $q->order_by = "date desc";
        $userTags = $q->Query($this->offset, $this->slice, "TABLE");
        if ($q->nb == 0) {
            $userTags = array();
        }

        $tags = array();
        /**
         * @var \DocUTag $uTag
         */
        foreach ($userTags as $uTag) {
            if ($uTag["tag"]) {
                $value = true;
                if ($uTag["comment"]) {
                    if ($json = json_decode($uTag["comment"])) {
                        $value = $json;
                    } else {
                        $value = $uTag["comment"];
                    }
                }

                $tags[] = array(
                    "id" => $uTag["tag"],
                    "date" => $uTag["date"],
                    "uri" => URLUtils::generateURL(sprintf(
                        "%s/%s/usertags/%s",
                        Settings::ApiV2,
                        $this->baseURL,
                        $this->_document->name ? $this->_document->name : $this->_document->initid,
                        $uTag["tag"]
                    )),

                    "value" => $value
                );
            }
        }

        $info["uri"] = URLUtils::generateURL(sprintf(
            "%s/%s/usertags/",
            Settings::ApiV2,
            $this->baseURL,
            $this->_document->name ? $this->_document->name : $this->_document->initid
        ));
        $info["requestParameters"] = array(
            "slice" => $this->slice,
            "offset" => $this->offset
        );

        $info["userTags"] = $tags;
        return $info;
    }


    /**
     * Find the current document and set it in the internal options
     *
     * @param $resourceId
     *
     * @throws Exception
     */
    protected function setDocument($resourceId)
    {
        $this->_document = DocManager::getDocument($resourceId);
        if (!$this->_document) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }

        if ($this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0219", $resourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setURI(DocumentUtils::getURI($this->_document));
            throw $exception;
        }
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $resourceId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
    }

    /**
     * Set limit of revision to send
     *
     * @param int $slice
     */
    public function setSlice($slice)
    {
        $this->slice = intval($slice);
    }

    /**
     * Set offset of revision to send
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = intval($offset);
    }

    /**
     * Generate the etag info for the current ressource
     *
     * @return null|string
     * @throws \Dcp\Db\Exception
     */
    public function getEtagInfo()
    {
        $id = $this->documentId;
        $id = DocManager::getIdentifier($id, true);
        $sql = sprintf("select id, date, comment from docutag where id = %d order by date desc limit 1", $id);
        DbManager::query($sql, $result, false, true);
        $result[] = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return join("", $result);
    }
}
