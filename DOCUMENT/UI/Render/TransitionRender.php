<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 20/02/15
 * Time: 17:35
 */

namespace Dcp\Ui;

class TransitionRender
{
    /**
     * @var \WDoc
     */
    protected $workflow;
    protected $attributeCount = 0;
    
    protected $workflowData;
    /**
     * @var \FormatCollection
     */
    protected $formatCollection;
    /**
     * @var \Dcp\HttpApi\V1\Crud\Document
     */
    protected $crudWorkflow;
    /**
     * @return \WDoc
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }
    /**
     * @param \WDoc $workflow
     */
    public function setWorkflow(\WDoc $workflow)
    {
        $this->workflow = $workflow;
    }
    /**
     * @return \Dcp\HttpApi\V1\Crud\Document
     */
    public function getViewWorkflow($key = null)
    {
        if ($this->workflowData === null) {
            $this->crudWorkflow = new \Dcp\HttpApi\V1\Crud\Document();
            
            $info = array(
                "document.properties",
                "document.properties.family"
            );
            
            $this->crudWorkflow->setDefaultFields(implode(",", $info));
            $this->workflowData = $this->crudWorkflow->getInternal($this->workflow);
        }
        if ($key !== null) {
            return $this->workflowData[$key];
        }
        return $this->crudWorkflow;
    }
    /**
     * @param $transitionId
     * @return array
     */
    public function getTransitionParameters($transitionId)
    {
        $transition = isset($this->workflow->transitions[$transitionId])?$this->workflow->transitions[$transitionId]:null;
        
        $askes = isset($transition["ask"]) ? $transition["ask"] : array();
        $addComment = empty($transition["nr"]);
        $workflow = $this->getViewWorkflow();
        
        $attrData = array();
        
        if (count($askes) > 0) {
            $askFrame = new \FieldSetAttribute("_workflow_fr_askes_", $this->workflow->id, ___("Workflow Parameters", "ddui") , "W", "N");
            $attrData[] = $this->getAttributeInfo($workflow, $askFrame);
            
            foreach ($askes as $ask) {
                $oa = $this->workflow->getAttribute($ask);
                if ($oa) {
                    $oa->fieldSet = $askFrame;
                    $attrData[] = $this->getAttributeInfo($workflow, $oa);
                    
                    if ($oa->type === "array") {
                        $attrs = $this->workflow->attributes->getArrayElements($oa->id);
                        
                        foreach ($attrs as $aid => $attr) {
                            
                            $attrData[] = $this->getAttributeInfo($workflow, $this->workflow->getAttribute($aid));
                        }
                    }
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
        if ($this->formatCollection === null) {
            $this->formatCollection = new \FormatCollection($this->workflow);
        }
        $info = $document->getAttributeInfo($attribute, $this->attributeCount++);
        if (!empty($attribute->fieldSet->id)) {
            $info["parent"] = $attribute->fieldSet->id;
        }
        $value = null;
        if ($attribute->usefor === "Q") {
            $value = $this->workflow->getFamilyParameterValue($attribute->id);
        } else {
            $value = $this->workflow->getRawValue($attribute->id);
        }
        if ($attribute->isNormal) {
            $info["attributeValue"] = $this->formatCollection->getInfo($attribute, $value, $this->workflow);
        }
        return $info;
    }
    /**
     * @return array
     */
    public function getTemplates($transitionId)
    {
        return array(
            "body" => "{{>transitionHeader}}{{>transitionAsk}}{{>transitionMessages}}{{>transitionButtons}}",
            "sections" => array(
                "transitionHeader" => '<div class="dcpChangeState--header"/>',
                "transitionMessages" => '<div class="dcpChangeState--messages"/>',
                "transitionAsk" => '<section class="dcpChangeState--ask dcpDocument__body"/>',
                "transitionButtons" => '<div class="dcpChangeState--buttons"/>'
            )
        );
    }
    /**
     * @return RenderOptions
     * @throws Exception
     */
    public function getRenderOptions($transitionId)
    {
        $options = new RenderOptions();
        $options->longtext("_workflow_comment_")->setPlaceHolder(___("Add a note to the history", "ddui"))->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame("_workflow_fr_comment_")->setLabelPosition(CommonRenderOptions::nonePosition);
        
        return $options;
    }
}
