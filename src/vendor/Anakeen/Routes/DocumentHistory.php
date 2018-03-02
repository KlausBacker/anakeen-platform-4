<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;
use Dcp\Core\ContextManager;
use Dcp\Core\DbManager;
use Dcp\Core\DocManager;
use Dcp\Core\Settings;

/**
 * Class DocumentHistory
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/history/
 * @package Anakeen\Routes\Core
 */
class DocumentHistory
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

        $revision = $request->getQueryParam("revision");
        if ($revision !== null) {
            $this->setRevisionFilter($revision);
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

        $search = $this->prepareSearchDoc();
        $documentList = $search->getDocumentList();


        $info["uri"] = URLUtils::generateURL(sprintf(
            "%s%s/%s/history/",
            Settings::ApiV2,
            $this->baseURL,
            $this->_document->name ? $this->_document->name : $this->_document->initid
        ));
        $info["requestParameters"] = array(
            "slice" => $this->slice,
            "offset" => $this->offset,
            "revision" => $this->revisionFilter
        );

        $revisionHistory = array();
        /**
         * @var \Doc $revision
         */
        foreach ($documentList as $revision) {
            $history = $revision->getHisto(false);
            foreach ($history as $k => $msg) {
                unset($history[$k]["id"]);
                unset($history[$k]["initid"]);
                $history[$k]["uid"] = intval($msg["uid"]);
                switch ($history[$k]["level"]) {
                    case \DocHisto::ERROR:
                        $history[$k]["level"] = "error";
                        break;

                    case \DocHisto::WARNING:
                        $history[$k]["level"] = "warning";
                        break;

                    case \DocHisto::MESSAGE:
                        $history[$k]["level"] = "message";
                        break;

                    case \DocHisto::INFO:
                        $history[$k]["level"] = "info";
                        break;

                    case \DocHisto::NOTICE:
                        $history[$k]["level"] = "notice";
                        break;
                }
            }
            $revisionHistory[] = array(
                "uri" => URLUtils::generateURL(sprintf(
                    "%s%s/%s/revisions/%d.json",
                    Settings::ApiV2,
                    $this->baseURL,
                    ($revision->name ? $revision->name : $revision->initid),
                    $revision->revision
                )),
                "properties" => array(
                    "id" => intval($revision->initid),
                    "title" => $revision->getTitle(),
                    "status" => ($revision->doctype == "Z") ? "deleted"
                        : (($revision->locked == -1) ? "fixed" : "alive"),
                    "revision" => intval($revision->revision),
                    "owner" => array(
                        "id" => $revision->owner,
                        "title" => \Account::getDisplayName($revision->owner)
                    ),
                    "state" => array(
                        "reference" => $revision->getState(),
                        "stateLabel" => ($revision->state) ? _($revision->state) : '',
                        "activity" => ($revision->getStateActivity() ? _($revision->getStateActivity())
                            : ($revision->state ? _($revision->state) : '')),
                        "color" => ($revision->state) ? $revision->getStateColor() : ''
                    ),

                    "version" => $revision->version,
                    "revisionDate" => strftime("%Y-%m-%d %T", $revision->revdate)
                ),
                "messages" => $history
            );
        }
        $info["history"] = $revisionHistory;
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

        if ($this->_family && !is_a($this->_document, sprintf("\\Dcp\\Family\\%s", $this->_family->name))) {
            $exception = new Exception("CRUD0220", $resourceId, $this->_family->name);
            $exception->setHttpStatus("404", "Document is not a document of the family " . $this->_family->name);
            throw $exception;
        }

        if ($this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0219", $resourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setURI(DocumentUtils::getURI($this->_document));
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
     * To return history of a specific revision
     *
     * @param int $revisionFilter
     */
    public function setRevisionFilter($revisionFilter)
    {
        $this->revisionFilter = intval($revisionFilter);
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
        $sql = sprintf("select id, date, comment from dochisto where id = %d order by date desc limit 1", $id);

        DbManager::query($sql, $result, false, true);
        $user = ContextManager::getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        // Necessary for localized state label
        $result[] = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return join("", $result);
    }

    /**
     * @return \SearchDoc
     */
    protected function prepareSearchDoc()
    {
        $search = new \SearchDoc();
        $search->addFilter("initid = %d", $this->_document->initid);
        $search->setOrder("revision desc");
        if ($this->revisionFilter >= 0) {
            $search->addFilter("revision = %d", $this->revisionFilter);
        }
        if ($this->slice > 0) {
            $search->setSlice($this->slice);
        }
        if ($this->offset > 0) {
            $search->setStart($this->offset);
        }
        $search->setObjectReturn();
        $search->latest = false;
        return $search;
    }
}
