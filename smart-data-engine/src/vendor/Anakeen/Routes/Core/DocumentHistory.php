<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Utils\Date;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;
use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\SmartElementManager;

/**
 * Class DocumentHistory
 *
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/history/
 * @package Anakeen\Routes\Core
 */
class DocumentHistory
{
    protected $baseURL = "smart-elements";
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $_document = null;
    /**
     * @var \Anakeen\Core\SmartStructure
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
            \Anakeen\Routes\Core\Lib\DocumentUtils::verifyFamily($args["family"], $this->_document);
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
         * @var \Anakeen\Core\Internal\SmartElement $revision
         */
        foreach ($documentList as $revision) {
            $wdoc = null;
            $state = array(
                "reference" => "",
                "stateLabel" => "",
                "activity" => "",
                "color" => ""
            );
            // If the Smart Structure has a workflow it will fill the state array
            if ($revision->wid) {
                /**
                 * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
                 */
                $wdoc = SEManager::getDocument($revision->wid);
                if ($wdoc && $revision->state) {
                    SEManager::cache()->addDocument($wdoc);
                    $stateLabel = $wdoc->getStateLabel($revision->state) ?: $revision->state;
                    $state = array(
                        "reference" => $revision->getState(),
                        "stateLabel" => $stateLabel,
                        "activity" => $wdoc->getActivity($revision->state, $stateLabel),
                        "color" => $revision->getStateColor()
                    );
                }
            }
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
                $history[$k]["date"][10] = 'T';
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
                    "id" => intval($revision->id),
                    "initid" => intval($revision->initid),
                    "title" => $revision->getTitle(),
                    "status" => ($revision->doctype == "Z") ? "deleted"
                        : (($revision->locked == -1) ? "fixed" : "alive"),
                    "revision" => intval($revision->revision),
                    "owner" => array(
                        "id" => $revision->owner,
                        "title" => \Anakeen\Core\Account::getDisplayName($revision->owner)
                    ),
                    "state" => $state,

                    "version" => $revision->version,
                    "revisionDate" => Date::rawToIsoDate($revision->mdate)
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
        $this->_document = SmartElementManager::getDocument($resourceId);
        if (!$this->_document) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
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
     * @throws \Anakeen\Database\Exception
     */
    public function getEtagInfo()
    {

        $id = $this->documentId;
        $id = SEManager::getIdentifier($id, true);
        $sql = sprintf("select id, date, comment from dochisto where id = %d order by date desc limit 1", $id);
        DbManager::query($sql, $result, false, true);


        $sql = sprintf("select mdate from docread where id = %d", $id);
        DbManager::query($sql, $mdate, true, true);

        $result[] = $mdate;
        $user = ContextManager::getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        // Necessary for localized state label
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");

        return join("", $result);
    }

    /**
     * @return \Anakeen\Search\Internal\SearchSmartData
     */
    protected function prepareSearchDoc()
    {
        $search = new \Anakeen\Search\Internal\SearchSmartData();
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
        if ($this->_document->doctype === "Z") {
            $search->trash = 'only';
        }
        $search->setObjectReturn();
        $search->latest = false;

        $search->search();
        return $search;
    }
}
