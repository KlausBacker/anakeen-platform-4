<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui\Crud;

use Dcp\Ui\CommonRenderOptions;
use Dcp\Ui\RenderOptions;

class WorkflowView extends \Dcp\HttpApi\V1\Crud\WorkflowState
{
    protected $attributeCount = 0;
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
        $info["transition"] = array(
            "id" => ($transitionId !== null) ? $transitionId : null,
            "currentState" => $this->getStateInfo($this->_document->state) ,
            "nextState" => $this->getStateInfo($this->state) ,
            "label" => isset($transitionId) ? _($transitionId) : ___("Invalid transition", "ddui") ,
            "askComment" => empty($transition["nr"]) ,
            "askAttributes" => $this->getAskAttributes(empty($transition["nr"]) , isset($transition["ask"]) ? $transition["ask"] : array())
        );
        $info["renderOptions"] = $this->getRenderOptions();
        $info["labels"] = array(
            "inprogress" => ___("In progress transition", "ddui") ,
            "confirm" => ($transitionId === null) ? ___("Force transition", "ddui") : ___("Confirm transition", "ddui") ,
            "cancel" => ___("Cancel transition", "ddui") ,
            "close" => ___("Close Transition Window", "ddui") ,
            "retry" => ___("Retry transition", "ddui")
        );
        return $info;
    }
    
    protected function getRenderOptions()
    {
        $options = new RenderOptions();
        $options->longtext("_workflow_comment_")->setPlaceHolder(___("Add a note to the history", "ddui"))->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame("_workflow_fr_comment_")->setLabelPosition(CommonRenderOptions::nonePosition);
        
        return $options;
    }
    protected function getAskAttributes($addComment, $askes)
    {
        
        $workflow = new \Dcp\HttpApi\V1\Crud\Document($this->workflow);
        
        $attrData = array();
        
        if (count($askes) > 0) {
            $askFrame = new \FieldSetAttribute("_workflow_fr_askes_", $this->workflow->id, ___("Workflow Parameters", "ddui") , "W", "N");
            $attrData[] = $this->getAttributeInfo($workflow, $askFrame);
            foreach ($askes as $ask) {
                $oa = $this->workflow->getAttribute($ask);
                if ($oa) {
                    $oa->fieldSet = $askFrame;
                    $attrData[] = $this->getAttributeInfo($workflow, $oa);
                }
            }
        }
        if ($addComment) {
            $frComment = new \FieldSetAttribute("_workflow_fr_comment_", $this->workflow->id, ___("Workflow Transition Comment", "ddui") , "W", "N");
            
            $commentAttr = new \NormalAttribute("_workflow_comment_", $this->workflow->id, ___("Transition Comment", "ddui") , "longtext", "", false, 10, "", "W", false, false, false, $frComment, "", "", "");
            $attrData[] = $this->getAttributeInfo($workflow, $frComment);
            $attrData[] = $this->getAttributeInfo($workflow, $commentAttr);
        }
        return $attrData;
    }
    protected function getAttributeInfo(\Dcp\HttpApi\V1\Crud\Document $document, \BasicAttribute $attribute)
    {
        $info = $document->getAttributeInfo($attribute, $this->attributeCount++);
        if (!empty($attribute->fieldSet->id)) {
            $info["parent"] = $attribute->fieldSet->id;
        }
        return $info;
    }
}
