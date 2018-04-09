<?php
namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\DocManager;
use Anakeen\Core\Settings;

/**
 * Class WorkflowTransitionCollection
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/workflows/transitions/
 * @package Anakeen\Routes\Core
 */
class WorkflowTransitionCollection extends WorkflowStateCollection
{
    /**
     * Get transition list
     *
     * @return mixed
     */
    public function doRequest()
    {
        
        $info = array();
        
        $baseUrl = URLUtils::generateURL(sprintf("%s/%s/workflows/", Settings::ApiV2, $this->baseURL, $this->_document->name ? $this->_document->name : $this->_document->initid));
        $info["uri"] = $baseUrl . "transitions/";

        $transitions = array();
        /**
         * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $workflow
         */
        $this->workflow = DocManager::getDocument($this->_document->wid);
        $this->workflow->set($this->_document);

        foreach ($this->workflow->transitions as $k => $transition) {
            $transitions[] = array(
                "uri" => sprintf("%s%s", $info["uri"], $k) ,
                "label" => _($k) ,
                "valid" => $this->isValidTransition($k)
            );
        }
        /**
         * @var \Doc $revision
         */

        $info["transitions"] = $transitions;

        return $info;
    }
    protected function isValidTransition($trId)
    {
        foreach ($this->workflow->cycle as $wTransition) {
            if (($wTransition["e1"] === $this->_document->state) && ($wTransition["t"] === $trId)) {
                return true;
            }
        }
        return false;
    }
}
