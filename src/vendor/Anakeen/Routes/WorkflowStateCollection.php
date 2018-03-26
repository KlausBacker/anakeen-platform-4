<?php
namespace Anakeen\Routes\Core;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\URLUtils;
use Anakeen\Core\DocManager;
use Dcp\Core\Settings;
use Anakeen\Router\Exception;

/**
 * Class WorkflowStateCollection
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/workflows/states/
 * @package Anakeen\Routes\Core
 */
class WorkflowStateCollection
{
    protected $baseURL = "documents";
    /**
     * @var \Doc
     */
    protected $_document = null;
    /**
     * @var \WDoc
     */
    protected $workflow = null;
    /**
     * @var \DocFam
     */
    protected $_family = null;
    /**
     * @var bool if true return all states else only followings
     */
    protected $allStates = false;
    protected $documentId;


    /**
     * List all availables states for a document
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        return ApiV2Response::withData($response, $this->doRequest());
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->allStates = !empty($request->getQueryParam("allState"));

        $this->documentId= $args["docid"];
        $this->setDocument($this->documentId);
        if (isset($args["family"])) {
            DocumentUtils::verifyFamily($args["family"], $this->_document);
        }
    }

    /**
     * Get state list
     *
     * @return mixed
     */
    public function doRequest()
    {
        
        $info = array();
        
        $baseUrl = URLUtils::generateURL(sprintf("%s/%s/workflows/", Settings::ApiV2, $this->baseURL, $this->_document->name ? $this->_document->name : $this->_document->initid));
        $info["uri"] = $baseUrl . "states/";
        
        $states = array();
        
        if ($this->allStates) {
            $wStates = $this->workflow->getStates();
        } else {
            $wStates = $this->workflow->getFollowingStates();
        }
        foreach ($wStates as $aState) {
            $transition = $this->workflow->getTransition($this->_document->state, $aState);
            if ($transition) {
                $controlTransitionError = $this->workflow->control($transition["id"]);
                $transitionData = array(
                    "id" => $transition["id"],
                    "uri" => sprintf("%stransitions/%s", $baseUrl, $transition["id"]) ,
                    "label" => _($transition["id"]) ,
                    "error" => $this->getM0($transition, $aState) ,
                    "authorized" => empty($controlTransitionError)
                );
            } else {
                $transitionData = null;
            }
            
            $state = $this->getStateInfo($aState);
            $state["uri"] = sprintf("%s%s", $info["uri"], $aState);
            
            $state["transition"] = $transitionData;
            
            $states[] = $state;
        }
        /**
         * @var \Doc $revision
         */
        
        $info["states"] = $states;
        return $info;
    }
    
    protected function getM0($tr, $state)
    {
        if ($tr && (!empty($tr["m0"]))) {
            // verify m0
            return call_user_func(array(
                $this->workflow,
                $tr["m0"],
            ), $state, $this->workflow->doc->state);
        }
        return null;
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
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $resourceId, $err);
            $exception->setHttpStatus("403", "Forbidden");
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
        /**
         * @var \WDoc $workflow
         */
        $this->workflow = DocManager::getDocument($this->_document->wid);
        $this->workflow->set($this->_document);
    }
    
    protected function getStateInfo($state)
    {
        if (empty($state)) {
            return null;
        }
        return array(
            "id" => $state,
            "label" => _($state) ,
            "activity" => $this->workflow->getActivity($state) ,
            "displayValue" => ($this->workflow->getActivity($state)) ? $this->workflow->getActivity($state) : _($state) ,
            "color" => $this->workflow->getColor($state)
        );
    }
}
