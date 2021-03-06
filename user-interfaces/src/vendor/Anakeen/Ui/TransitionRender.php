<?php /** @noinspection PhpUnusedParameterInspection */

namespace Anakeen\Ui;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

class TransitionRender
{
    const commentAttribute = "_workflow_comment_";
    const commentFrameAttribute = "_workflow_fr_comment_";
    const parameterFrameAttribute = "_workflow_fr_askes_";
    /**
     * @var WDocHooks
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
    protected $instanceData;

    /**
     * @return WDocHooks
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
        $attrData = array();
        $askes = [];
        if (!$transitionId) {
            // Always ask reason for non valid transition
            $addComment = true;
        } else {
            $transition = $this->workflow->getTransition($transitionId);
            if ($transition) {
                $askes = $transition->getAsks();
            }
            $addComment = $transition->getRequiredComment();
        }
        $workflow = $this->getViewWorkflow();

        if (count($askes) > 0) {
            $transitionLabel = isset($transitionId) ? $this->workflow->getTransitionLabel($transitionId) : ___(
                "Invalid transition",
                "ddui"
            );
            $askFrame = new \Anakeen\Core\SmartStructure\FieldSetAttribute(
                self::parameterFrameAttribute,
                $this->workflow->id,
                sprintf(___("Workflow Parameters : %s", "ddui"), $transitionLabel),
                BasicAttribute::READWRITE_ACCESS,
                "N"
            );
            $attrData[] = $this->getAttributeInfo($workflow, $askFrame);
            $this->workflow->attributes->addAttribute($askFrame);
            $instance = $this->workflow->getSmartElement();
            foreach ($askes as $oa) {
                if ($oa) {
                    $oa->fieldSet = $askFrame;
                    if (in_array(SEManager::getIdFromName($oa->docname), $instance->attributes->fromids)) {
                        $attrData[] = $this->getAttributeInfo($this->getViewInstancew(), $oa);
                    } else {
                        $attrData[] = $this->getAttributeInfo($workflow, $oa);
                    }

                    if ($oa->type === "array") {
                        if ($this->workflow->getAttribute($oa->id) &&
                            in_array($oa->structureId, $this->workflow->getFromDoc())) {
                            $attrs = $this->workflow->attributes->getArrayElements($oa->id);
                            foreach ($attrs as $aid => $attr) {
                                $attrData[] = $this->getAttributeInfo($workflow, $this->workflow->getAttribute($aid));
                            }
                        } elseif (in_array(SEManager::getIdFromName($oa->docname), $instance->attributes->fromids)) {
                            $attrs = $this->workflow->getSmartElement()->attributes->getArrayElements($oa->id);
                            foreach ($attrs as $aid => $attr) {
                                $attrData[] = $this->getAttributeInfo(
                                    $this->getViewInstancew(),
                                    $instance->getAttribute($aid)
                                );
                            }
                        }
                    }
                }
            }
        }

        if ($addComment) {
            $frComment = new \Anakeen\Core\SmartStructure\FieldSetAttribute(
                self::commentFrameAttribute,
                $this->workflow->id,
                ___("Workflow Transition Comment", "ddui"),
                BasicAttribute::READWRITE_ACCESS,
                "N"
            );

            $commentAttr = new \Anakeen\Core\SmartStructure\NormalAttribute(
                self::commentAttribute,
                $this->workflow->id,
                ___("Transition Comment", "ddui"),
                "longtext",
                "",
                false,
                10,
                "",
                BasicAttribute::READWRITE_ACCESS,
                false,
                false,
                false,
                $frComment,
                "",
                "",
                ""
            );
            $attrData[] = $this->getAttributeInfo($workflow, $frComment);
            $attrData[] = $this->getAttributeInfo($workflow, $commentAttr);
        }
        return $attrData;
    }
    protected function getViewInstancew()
    {
        if ($this->instanceData === null) {
            $this->instanceData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->workflow->getSmartElement());

            $info = array(
                "document.properties",
                "document.properties.family"
            );

            $this->instanceData->setFields($info);
            $this->instanceData->getDocumentData();
        }

        return $this->instanceData;
    }
    /**
     * @param \Anakeen\Routes\Core\Lib\DocumentApiData $document
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $attribute
     * @return AttributeInfo
     */
    protected function getAttributeInfo(
        \Anakeen\Routes\Core\Lib\DocumentApiData $document,
        \Anakeen\Core\SmartStructure\BasicAttribute $attribute
    ) {
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

        $origin = $this->workflow;
        if ($this->workflow->getSmartElement()->getAttribute($attribute->id)) {
            $origin = $this->workflow->getSmartElement();
            if ($attribute->type === "docid" || $attribute->type === "account") {
                if (!$aInfo->getAutocomplete()) {
                    $aInfo->setAutocomplete([
                        "url" => sprintf("/api/v2/smart-elements/%d/autocomplete/%s", $origin->id, $attribute->id)
                    ]);
                }
            }
        }

        if ($attribute->usefor === "Q") {
            $value = $origin->getFamilyParameterValue($attribute->id);
        } else {
            $value = $origin->getRawValue($attribute->id);
        }
        if ($attribute->isNormal) {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute
             */
            $aInfo->setAttributeValue($this->formatCollection->getInfo($attribute, $value, $origin));
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
        $options->longtext(self::commentAttribute)->setPlaceHolder(___(
            "Add a note to the history",
            "ddui"
        ))->setLabelPosition(CommonRenderOptions::nonePosition);
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
