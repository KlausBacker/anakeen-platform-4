<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

/**
 * Class WorkflowTransition
 *
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/workflows/transitions/{transition}
 * @note    Used by route : GET /api/v2/smart-structures/{family}documents/{docid}/workflows/transitions/{transition}
 * @package Anakeen\Routes\Core
 */
class WorkflowTransition extends WorkflowState
{

    protected $transition = null;
    /**
     * @var WDocHooks
     */
    protected $workflow = null;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["docid"];
        $this->transition = $args["transition"];
        $this->setDocument($this->documentId);


        if (isset($args["family"])) {
            \Anakeen\Routes\Core\Lib\DocumentUtils::verifyFamily($args["family"], $this->_document);
        }

        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $this->documentId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
        /**
         * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $workflow
         */
        $this->workflow = SEManager::getDocument($this->_document->wid);
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
        $info["uri"] = sprintf("%stransitions/%s", $baseUrl, $this->transition);

        $transition = isset($this->workflow->transitions[$this->transition])
            ? $this->workflow->transitions[$this->transition] : null;

        if ($transition === null) {
            $exception = new Exception("CRUD0229", $this->transition, $this->workflow->title, $this->workflow->id);
            $exception->setHttpStatus("404", "Transition not found");
            throw $exception;
        }

        $nextState = '';
        foreach ($this->workflow->cycle as $wTransition) {
            if (($wTransition["e1"] === $this->_document->state) && ($wTransition["t"] === $this->transition)) {
                $nextState = $wTransition["e2"];
            }
        }
        /**
         * @var \Anakeen\Core\Internal\SmartElement $revision
         */
        $info["transition"] = array(
            "id" => $this->transition,
            "beginState" => $this->getStateInfo($this->_document->state),
            "endState" => $this->getStateInfo($nextState),
            "label" => _($this->transition),
            "askComment" => empty($transition["nr"]),
            "askAttributes" => $this->getAskAttributes(isset($transition["ask"]) ? $transition["ask"] : array())
        );
        return $info;
    }


    protected function getState()
    {
        foreach ($this->workflow->cycle as $wTransition) {
            if (($wTransition["e1"] === $this->_document->state) && ($wTransition["t"] === $this->transition)) {
                return $wTransition["e2"];
            }
        }
        return null;
    }

    protected function getAskAttributes($askes)
    {
        if (empty($askes)) {
            return array();
        }
        $workflow = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->workflow);

        $attrData = array();
        foreach ($askes as $ask) {
            $oa = $this->workflow->getAttribute($ask);
            if ($oa) {
                $attrData[] = $workflow->getAttributeInfo($oa);
            }
        }
        return $attrData;
    }
}
