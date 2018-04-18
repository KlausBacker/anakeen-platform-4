<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Created by PhpStorm.
 * User: eric
 * Date: 20/02/15
 * Time: 17:35
 */

namespace Dcp\Ui;

use Anakeen\Routes\Core\Lib\DocumentApiData;

class TransitionRender
{
    const commentAttribute = "_workflow_comment_";
    const commentFrameAttribute = "_workflow_fr_comment_";
    const parameterFrameAttribute = "_workflow_fr_askes_";
    /**
     * @var \WDoc
     */
    protected $workflow;
    protected $attributeCount = 0;

    protected $workflowData;
    /**
     * @var \Anakeen\Core\Internal\FormatCollection 
     */
    protected $formatCollection;
    /**
     * @var \Anakeen\Routes\Core\Lib\DocumentApiData
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
     * @param \Anakeen\SmartStructures\Wdoc\WDocHooks $workflow
     */
    public function setWorkflow(\Anakeen\SmartStructures\Wdoc\WDocHooks $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * @param string $key optionnal specific information
     *
     * @return \Anakeen\Routes\Core\Lib\DocumentApiData
     */
    public function getViewWorkflow($key = null)
    {
        if ($this->workflowData === null) {
            $this->crudWorkflow = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->workflow);

            $info = array(
                "document.properties",
                "document.properties.family"
            );

            $this->crudWorkflow->setFields($info);
            $this->workflowData = $this->crudWorkflow->getDocumentData();
        }
        if ($key !== null) {
            return $this->workflowData[$key];
        }
        return $this->crudWorkflow;
    }

    /**
     * @param $transitionId
     * @return AttributeInfo[]
     */
    public function getTransitionParameters($transitionId)
    {
        $transition = isset($this->workflow->transitions[$transitionId]) ? $this->workflow->transitions[$transitionId] : null;

        $askes = isset($transition["ask"]) ? $transition["ask"] : array();
        $addComment = empty($transition["nr"]);
        $workflow = $this->getViewWorkflow();

        $attrData = array();

        if (count($askes) > 0) {
            $transitionLabel = isset($transitionId) ? _($transitionId) : ___("Invalid transition", "ddui");
            $askFrame = new \Anakeen\Core\SmartStructure\FieldSetAttribute(self::parameterFrameAttribute, $this->workflow->id, sprintf(___("Workflow Parameters : %s", "ddui"), $transitionLabel), "W", "N");
            $attrData[] = $this->getAttributeInfo($workflow, $askFrame);
            $this->workflow->attributes->addAttribute($askFrame);
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
            $frComment = new \Anakeen\Core\SmartStructure\FieldSetAttribute(self::commentFrameAttribute, $this->workflow->id, ___("Workflow Transition Comment", "ddui"), "W", "N");

            $commentAttr = new \Anakeen\Core\SmartStructure\NormalAttribute(self::commentAttribute, $this->workflow->id, ___("Transition Comment", "ddui"), "longtext", "", false, 10, "", "W", false, false, false, $frComment, "", "", "");
            $attrData[] = $this->getAttributeInfo($workflow, $frComment);
            $attrData[] = $this->getAttributeInfo($workflow, $commentAttr);
        }
        return $attrData;
    }

    /**
     * @param \Anakeen\Routes\Core\Lib\DocumentApiData $document
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $attribute
     * @return AttributeInfo
     */
    protected function getAttributeInfo(\Anakeen\Routes\Core\Lib\DocumentApiData $document, \Anakeen\Core\SmartStructure\BasicAttribute $attribute)
    {
        if ($this->formatCollection === null) {
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection($this->workflow);
        }
        $aInfo = new AttributeInfo();

        $info = $document->getAttributeInfo($attribute, $this->attributeCount++);
        $aInfo->importData($info);

        if (!empty($attribute->fieldSet->id)) {
            $aInfo->setParent($attribute->fieldSet->id);
        }
        $value = null;
        if ($attribute->usefor === "Q") {
            $value = $this->workflow->getFamilyParameterValue($attribute->id);
        } else {
            $value = $this->workflow->getRawValue($attribute->id);
        }
        if ($attribute->isNormal) {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute
             */
            $aInfo->setAttributeValue($this->formatCollection->getInfo($attribute, $value, $this->workflow));
        }
        return $aInfo;
    }

    /**
     * @param string $transitionId transition identifier
     * @return array
     */
    public function getTemplates($transitionId)
    {
        return array(
            "body" => "{{>transitionHeader}}{{>transitionAsk}}{{>transitionMessages}}{{>transitionButtons}}",
            "sections" => array(
                "transitionHeader" => '<div class="dcpTransition--header"/>',
                "transitionMessages" => '<div class="dcpTransition--messages"/>',
                "transitionAsk" => '<section class="dcpTransition--ask dcpDocument__body"/>',
                "transitionButtons" => '<div class="dcpTransition--buttons"/>'
            )
        );
    }

    /**
     * @param string $transitionId transition identifier
     * @return RenderOptions
     * @throws Exception
     */
    public function getRenderOptions($transitionId)
    {
        $default = new RenderDefault();

        $options = $default->getOptions($this->workflow);
        $options->longtext(self::commentAttribute)->setPlaceHolder(___("Add a note to the history", "ddui"))->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame(self::commentFrameAttribute)->setLabelPosition(CommonRenderOptions::nonePosition);

        return $options;
    }

    /**
     * Add custom css file references
     * @param string $transitionId transition identifier
     * @return array
     */
    public function getCssReferences($transitionId)
    {
        return array();
    }

    /**
     * Add custom js file references
     * @param string $transitionId transition identifier
     * @return array
     */
    public function getJsReferences($transitionId)
    {
        return array();
    }
}
