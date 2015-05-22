<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui\Crud;

class TransitionView extends \Dcp\HttpApi\V1\Crud\WorkflowState
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
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @return mixed
     */
    public function create()
    {
        $info = parent::create();
        $info["labels"] = array(
            "close" => ___("Close Transition Window", "ddui") ,
            "success" => ___("Transition success", "ddui")
        );
        $info["workflow"] = $this->getWorkflowData();
        return $info;
    }
    /**
     * Get ressource
     *
     * @param string $resourceId Resource identifier
     * @throws  \Dcp\HttpApi\V1\Crud\Exception
     * @return mixed
     */
    public function read($resourceId)
    {
        $info = parent::read($resourceId);
        
        $transition = $transitionId = null;
        foreach ($this->workflow->cycle as $wTransition) {
            if (($wTransition["e1"] === $this->_document->state) && ($wTransition["e2"] === $this->state)) {
                $transitionId = $wTransition["t"];
                $transition = isset($this->workflow->transitions[$transitionId]) ? $this->workflow->transitions[$transitionId] : null;
            }
        }
        if (is_a($this->workflow, 'Dcp\Ui\IRenderTransitionAccess')) {
            $render = $this->workflow->getTransitionRender($transitionId);
        } else {
            $render = new \Dcp\Ui\TransitionRender();
        }
        $render->setWorkflow($this->workflow);
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
        $workflow = new \Dcp\HttpApi\V1\Crud\Document();
        
        $info = array(
            "document.properties",
            "document.properties.family"
        );
        
        $workflow->setDefaultFields(implode(",", $info));
        $this->workflowData = $workflow->getInternal($this->workflow);
        return $workflow;
    }
}
