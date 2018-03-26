<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\DocManager;
use Dcp\Core\Settings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class WorkflowState
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/workflows/states/{state}
 * @note    Used by route : GET /api/v2/families/{family}/documents/{docid}/workflows/states/{state}
 * @package Anakeen\Routes\Core
 */
class WorkflowState
{
    protected $baseURL = "documents";
    /**
     * @var \Doc
     */
    protected $_document = null;
    /**
     * @var string workflow state asked
     */
    protected $state = null;
    /**
     * @var \WDoc
     */
    protected $workflow = null;
    /**
     * @var string|int
     */
    protected $documentId = 0;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        return ApiV2Response::withData($response, $this->doRequest());
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["docid"];
        $this->state = $args["state"];
        $this->setDocument($this->documentId);
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $this->documentId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }

        if (isset($args["family"])) {
            DocumentUtils::verifyFamily($args["family"], $this->_document);
        }

        /**
         * @var \WDoc $workflow
         */
        $this->workflow = DocManager::getDocument($this->_document->wid);
        $this->workflow->set($this->_document);
    }


    protected function doRequest(&$messages = [])
    {
        $info = array();


        $baseUrl = URLUtils::generateURL(sprintf(
            "%s%s/%s/workflows/",
            Settings::ApiV2,
            $this->baseURL,
            $this->_document->name ? $this->_document->name : $this->_document->initid
        ));
        $info["uri"] = sprintf("%sstates/%s", $baseUrl, $this->getState());

        $allStates = $this->workflow->getStates();

        $state = isset($allStates[$this->getState()]) ? $allStates[$this->getState()] : null;
        if ($state === null) {
            $exception = new Exception("CRUD0228", $this->getState(), $this->workflow->title, $this->workflow->id);
            $exception->setHttpStatus("404", "State not found");
            throw $exception;
        }

        $transition = $this->workflow->getTransition($this->_document->state, $state);
        if ($transition) {
            $transitionData = array(
                "uri" => sprintf("%stransitions/%s", $baseUrl, $transition["id"]),
                "label" => _($transition["id"])
            );
        } else {
            $transitionData = null;
        }
        /**
         * @var \Doc $revision
         */

        $info["state"] = $this->getStateInfo($state);
        $info["state"]["transition"] = $transitionData;
        return $info;
    }




    protected function getStateInfo($state)
    {
        if (empty($state)) {
            return null;
        }
        return array(
            "id" => $state,
            "isCurrentState" => ($state === $this->_document->state),
            "label" => _($state),
            "activity" => $this->workflow->getActivity($state),
            "displayValue" => ($this->workflow->getActivity($state)) ? $this->workflow->getActivity($state) : _($state),
            "color" => $this->workflow->getColor($state)
        );
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

        if ($this->_document->wid == 0) {
            $exception = new Exception("CRUD0227", $resourceId);
            $exception->setHttpStatus("404", "No workflow detected");
            throw $exception;
        }
        if ($this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0219", $resourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setURI(DocumentUtils::getURI($this->_document));
            throw $exception;
        }
    }

    protected function getState()
    {
        return $this->state;
    }
}
