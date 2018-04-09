<?php
namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\Lib\DocumentApiData;
use Anakeen\Routes\Core\WorkflowState;

/**
 * Class TransitionView
 * @note    Used by route : GET /api/v2/documents/{docid}/views/states/{state}
 * @package Anakeen\Routes\Ui
 */
class TransitionView extends WorkflowState
{
    protected $attributeCount = 0;
    protected $workflowData;
    /**
     * @var \WDoc|\Dcp\Ui\IRenderTransitionAccess
     */
    protected $workflow;
    /**
     * @var \FormatCollection
     */
    protected $formatCollection;
    
    /**
     * Change state
     */
    public function doRequest()
    {
        $info = parent::doRequest();
        
        $transition = $transitionId = null;
        foreach ($this->workflow->cycle as $wTransition) {
            if (($wTransition["e1"] === $this->_document->state) && ($wTransition["e2"] === $this->state)) {
                $transitionId = $wTransition["t"];
                $transition = isset($this->workflow->transitions[$transitionId]) ? $this->workflow->transitions[$transitionId] : null;
            }
        }
        
        $render = \Dcp\Ui\RenderConfigManager::getTransitionRender($transitionId, $this->workflow);
        
        $info["transition"] = array(
            "id" => ($transitionId !== null) ? $transitionId : null,
            "beginState" => $this->getStateInfo($this->_document->state) ,
            "endState" => $this->getStateInfo($this->state) ,
            "label" => isset($transitionId) ? _($transitionId) : ___("Invalid transition", "ddui") ,
            "askComment" => empty($transition["nr"]) ,
            "askAttributes" => $render->getTransitionParameters($transitionId)
        );
        
        $info["css"] = $this->getStyleData($render->getCssReferences($transitionId));
        $info["js"] = $this->getStyleData($render->getJsReferences($transitionId));
        $info["templates"] = $render->getTemplates($transitionId);
        $info["renderOptions"] = $render->getRenderOptions($transitionId);
        $info["labels"] = array(
            "inprogress" => ___("In progress transition", "ddui") ,
            "confirm" => ($transitionId === null) ? ___("Force transition", "ddui") : ___("Confirm transition", "ddui") ,
            "cancel" => ___("Cancel transition", "ddui") ,
            "close" => ___("Close Transition Window", "ddui") ,
            "retry" => ___("Retry transition", "ddui")
        );
        
        $info["workflow"] = $render->getViewWorkflow("document");
        return $info;
    }
    /**
     * @param array $list indexed array
     * @return array|bool
     */
    protected function getStyleData($list)
    {
        $pathArray = array();
        foreach ($list as $id => $path) {
            $pathArray[] = array(
                "path" => $path,
                "key" => $id
            );
        }
        
        return $pathArray;
    }
    protected function getWorkflowData()
    {
        
        if ($this->workflowData === null) {
            $this->getWorkflowDataObject();
        }
        return $this->workflowData["document"];
    }
    
    protected function getWorkflowDataObject()
    {
        $workflow = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->workflow);
        
        $info = array(
            "document.properties",
            "document.properties.family"
        );
        $workflow->setFields($info);
        $this->workflowData = $workflow->getDocumentData();
        return $workflow;
    }
}
