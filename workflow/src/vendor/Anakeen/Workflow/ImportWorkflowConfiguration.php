<?php

namespace Anakeen\Workflow;

use Anakeen\Core\SEManager;

use Anakeen\Core\Utils\Xml;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Ui\ImportRenderConfiguration;
use Anakeen\Exception;
use SmartStructure\Fields\Mailtemplate as MailFields;
use SmartStructure\Fields\Timer as TimerFields;
use SmartStructure\Fields\Wdoc as WdocFields;

class ImportWorkflowConfiguration extends ImportRenderConfiguration
{
    protected $mtPrefix;
    protected $tmPrefix;
    protected $wflPrefix;

    protected function importDataElements()
    {
        $data = parent::importDataElements();
        $data = array_merge($data, $this->importMailTemplates());
        $data = array_merge($data, $this->importTimers());
        $data = array_merge($data, $this->importWorkflowInstances());
        return $data;
    }

    /* Import Mail Templates section */

    /**
     * Import mailtemplate:mailtemplate xml tags
     * @return array
     */
    protected function importMailTemplates()
    {
        $this->mtPrefix = Xml::getPrefix($this->dom, ExportWorkflowConfiguration::NSMTURL);
        $configs = $this->getMailNodes($this->dom->documentElement, "mailtemplate");
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importMailTemplate($config));
        }
        return $data;
    }

    protected function importMailtemplate(\DOMElement $mailNode)
    {
        $mailtemplate = SEManager::createDocument("MAILTEMPLATE");

        $name = $mailNode->getAttribute("name");
        if ($name) {
            $mailtemplate->name = $name;

            $this->setEltValue($mailtemplate, $mailNode->getAttribute("structure"), MailFields::tmail_family);
            $this->setEltValue($mailtemplate, $mailNode->getAttribute("label"), MailFields::tmail_title);
            $this->setEltValue($mailtemplate, $mailNode->getAttribute("workflow-model"), MailFields::tmail_workflow);
            $this->setEltValue($mailtemplate, $this->evaluate($mailNode, "string({$this->mtPrefix}:subject)"), MailFields::tmail_subject);
            $this->setEltValue($mailtemplate, $this->evaluate($mailNode, "string({$this->mtPrefix}:body)"), MailFields::tmail_body);
            $this->setEltValue($mailtemplate, $this->evaluate($mailNode, "string({$this->mtPrefix}:use-html-anchor)") === "true" ? "yes" : "no", MailFields::tmail_ulink);
            $this->setEltValue($mailtemplate, $this->evaluate($mailNode, "string({$this->mtPrefix}:savecopy)") === "true" ? "yes" : "no", MailFields::tmail_savecopy);

            $fromNodes = $this->evaluate($mailNode, "({$this->mtPrefix}:from/*)[1]");
            foreach ($fromNodes as $fromNode) {
                $this->getRecipient($fromNode, $destType, $destValue);
                if ($destType && $destValue) {
                    $mailtemplate->setValue(MailFields::tmail_fromtype, [$destType]);
                    $mailtemplate->setValue(MailFields::tmail_from, [$destValue]);
                }
            }


            /** @var \DOMNodeList $recipNodes */
            $recipNodes = $this->evaluate($mailNode, "({$this->mtPrefix}:recipients/{$this->mtPrefix}:recipient)");

            if ($recipNodes->length > 0) {
                $mailtemplate->clearArrayValues(MailFields::tmail_dest);
            }

            /** @var \DOMElement $recipNode */
            foreach ($recipNodes as $recipNode) {
                $destNodes = $this->evaluate($recipNode, "({$this->mtPrefix}:*)");

                $copyMode = $recipNode->getAttribute("dest");
                if ($copyMode) {
                    foreach ($destNodes as $destNode) {
                        $this->getRecipient($destNode, $destType, $destValue);

                        if ($destType && $destValue) {
                            $mailtemplate->addArrayRow(MailFields::tmail_dest, [
                                MailFields::tmail_desttype => $destType,
                                MailFields::tmail_recip => $destValue,
                                MailFields::tmail_copymode => $copyMode
                            ]);
                        }
                    }
                }
            }
            /** @var \DOMNodeList $attachNodes */
            $attachNodes = $this->evaluate($mailNode, "({$this->mtPrefix}:attachments/{$this->mtPrefix}:attachment)");
            if ($attachNodes->length > 0) {
                $attachData = [];
                /** @var \DOMElement $attachNode */
                foreach ($attachNodes as $attachNode) {
                    $label = $attachNode->getAttribute("label");
                    if ($label) {
                        $attachData[] = sprintf("%s (%s)", $attachNode->nodeValue, $label);
                    } else {
                        $attachData[] = $attachNode->nodeValue;
                    }
                }
                $mailtemplate->setValue(MailFields::tmail_attach, $attachData);
            }

            $this->verboseMessages[]=sprintf("[%s] Record \"%s\"", $mailtemplate->fromname, $mailtemplate->name);
            return $this->getElementdata($mailtemplate);
        }
        return [];
    }

    protected function getRecipient(\DOMElement $element, &$destType, &$destValue)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($prefix, $type) = explode(":", $element->nodeName);
        switch ($type) {
            case "config-parameter":
                $destType = "P";
                $destValue = sprintf("%s::%s", $element->getAttribute("ns"), $element->nodeValue);
                break;
            case "address":
                $destType = "F";
                $destValue = $element->nodeValue;
                break;
            case "element-field-value":
                $destType = "A";
                $destValue = $element->nodeValue;
                break;
            case "element-account-field":
                $destType = "D";
                $destValue = $element->nodeValue;
                break;
            case "structure-parameter-value":
                $destType = "E";
                $destValue = $element->nodeValue;
                break;
            case "workflow-field-value":
                $destType = "WA";
                $destValue = $element->nodeValue;
                break;
            case "workflow-parameter-value":
                $destType = "WE";
                $destValue = $element->nodeValue;
                break;
            case "workflow-account-field":
                $destType = "WD";
                $destValue = $element->nodeValue;
                break;
            case "element-name":
                $destType = "RD";
                $destValue = $element->nodeValue;
                break;
        }
        $label = $element->getAttribute("label");
        if ($label && $label !== $destValue) {
            $destValue = sprintf("%s (%s)", $destValue, $label);
        }
    }

    /* Import Timers section   */

    /**
     * Import timer:timer xml tags
     * @return array
     */
    protected function importTimers()
    {
        $this->tmPrefix = Xml::getPrefix($this->dom, ExportWorkflowConfiguration::NSTMURL);
        $configs = $this->getTimerNodes($this->dom->documentElement, "timer");
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importTimer($config));
        }
        return $data;
    }

    protected function importTimer(\DOMElement $timerNode)
    {
        $timer = SEManager::createDocument("TIMER");

        $name = $timerNode->getAttribute("name");
        if ($name) {
            $timer->name = $name;

            $this->setEltValue($timer, $timerNode->getAttribute("structure"), TimerFields::tm_family);
            $this->setEltValue($timer, $timerNode->getAttribute("label"), TimerFields::tm_title);
            $this->setEltValue($timer, $timerNode->getAttribute("workflow"), TimerFields::tm_workflow);
            $this->setEltValue($timer, $this->evaluate($timerNode, "string({$this->tmPrefix}:field-date-reference/@ref)"), TimerFields::tm_dyndate);
            $this->setEltValue($timer, $this->evaluate($timerNode, "string({$this->tmPrefix}:field-date-reference/@delta)"), TimerFields::tm_deltainterval);

            /** @var \DOMNodeList $taskNodes */
            $taskNodes = $this->evaluate($timerNode, "{$this->tmPrefix}:tasks/{$this->tmPrefix}:task");

            if ($taskNodes->length > 0) {
                $timer->clearArrayValues(TimerFields::tm_t_config);
                /** @var \DOMElement $taskNode */
                foreach ($taskNodes as $taskNode) {
                    /** @var \DOMNodeList $actionNodes */
                    $actionNodes = $this->evaluate($taskNode, "{$this->tmPrefix}:*");

                    if ($actionNodes->length > 0) {
                        $delta = $taskNode->getAttribute("delta");

                        /** @var \DOMNodeList $mails */
                        $mails = $this->evaluate($taskNode, "({$this->tmPrefix}:sendmail/@ref)");

                        $mailRefs = [];
                        foreach ($mails as $mail) {
                            /** @var \DOMElement $mail */
                            if ($mail->nodeValue) {
                                $mailRefs[] = $mail->nodeValue;
                            }
                        }
                        $method = "";
                        $state = $this->evaluate($taskNode, "string({$this->tmPrefix}:setstate/@state)");
                        /** @var \DOMNodeList $processesNode */
                        $processesNode = $this->evaluate($taskNode, "({$this->tmPrefix}:process)[1]");
                        if ($processesNode->length > 0) {
                            $processNode = $processesNode->item(0);
                            $function = $this->evaluate($processNode, "string({$this->tmPrefix}:process-callable/@function)");
                            $argNodes = $this->evaluate($processNode, "{$this->tmPrefix}:process-argument");
                            $args = [];
                            foreach ($argNodes as $argsNode) {
                                /** @var \DOMElement $argsNode */
                                $argType = $argsNode->getAttribute("type");

                                if ($argType === "string") {
                                    $args[] = sprintf('"%s"', str_replace('"', '\\"', $argsNode->nodeValue));
                                } else {
                                    $args[] = $argsNode->nodeValue;
                                }
                            }
                            $method = sprintf("%s(%s)", $function, implode(", ", $args));
                        }

                        $timer->addArrayRow(TimerFields::tm_t_config, [
                            TimerFields::tm_delay => 0,
                            TimerFields::tm_hdelay => 0,
                            TimerFields::tm_taskinterval => $delta,
                            TimerFields::tm_iteration => 1,
                            TimerFields::tm_tmail => $mailRefs,
                            TimerFields::tm_state => $state,
                            TimerFields::tm_method => $method
                        ]);
                    }
                }
            }

            $this->verboseMessages[]=sprintf("[%s] Record \"%s\"", $timer->fromname, $timer->name);
            return $this->getElementdata($timer);
        }
        return [];
    }

    /* Import Workflow section   */

    /**
     * Import workflow:config xml tags
     * @return array
     */
    protected function importWorkflowInstances()
    {
        $this->wflPrefix = Xml::getPrefix($this->dom, ExportWorkflowConfiguration::NSWURL);
        $configs = $this->getWorkflowNodes($this->dom->documentElement, "config");
        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importWorkflowInstance($config));
        }
        return $data;
    }

    protected function importWorkflowInstance(\DOMElement $workflowNode)
    {
        $name = $workflowNode->getAttribute("name");
        if ($name) {
            /**
             * @var WDocHooks $workflow
             */
            $model = $workflowNode->getAttribute("model");

            if (!$model) {
                throw new Exception("Workflow model not set");
            }
            $workflow = SEManager::createDocument($model);
            if (!$model) {
                throw new Exception(sprintf("Cannot create worflow from \"%s\"", $model));
            }


            $workflow->name = $name;

            $this->setEltValue($workflow, $workflowNode->getAttribute("structure"), WdocFields::wf_famid);
            $this->setEltValue($workflow, $workflowNode->getAttribute("label"), WdocFields::ba_title);

            $stepNodes = $this->evaluate($workflowNode, "{$this->wflPrefix}:steps/{$this->wflPrefix}:step");

            /** @var \DOMElement $stepNode */
            foreach ($stepNodes as $stepNode) {
                $state = $stepNode->getAttribute("ref");
                /** @var \DOMNodeList $nodeRefs */
                $nodeRefs = $this->evaluate($stepNode, "{$this->wflPrefix}:mailtemplate/@ref");
                $refs = $this->getNodeValues($nodeRefs);
                if ($refs) {
                    $workflow->setStateMailTemplate($state, $refs);
                }

                /** @var string $ref */
                $ref = $this->evaluate($stepNode, "string({$this->wflPrefix}:timer/@ref)");
                if ($ref) {
                    $workflow->setStateTimer($state, $ref);
                }

                $ref = $this->evaluate($stepNode, "string({$this->wflPrefix}:color)");
                if ($ref) {
                    $workflow->setStateColor($state, $ref);
                }
                $ref = $this->evaluate($stepNode, "string({$this->wflPrefix}:view-control/@ref)");
                if ($ref) {
                    $workflow->setStateViewControl($state, $ref);
                }
                $ref = $this->evaluate($stepNode, "string({$this->wflPrefix}:field-access-configuration/@ref)");
                if ($ref) {
                    $workflow->setStateFall($state, $ref);
                }
                $ref = $this->evaluate($stepNode, "string({$this->wflPrefix}:mask/@ref)");
                if ($ref) {
                    $workflow->setStateMask($state, $ref);
                }

                $ref = $this->evaluate($stepNode, "string({$this->wflPrefix}:element-access-configuration/@ref)");
                if ($ref) {
                    $workflow->setStateProfil($state, $ref);
                }
            }

            $transitionNodes = $this->evaluate($workflowNode, "{$this->wflPrefix}:transitions/{$this->wflPrefix}:transition");

            /** @var \DOMElement $transitionNode */
            foreach ($transitionNodes as $transitionNode) {
                $transitionId = $transitionNode->getAttribute("ref");
                /** @var \DOMNodeList $nodeRefs */
                $nodeRefs = $this->evaluate($transitionNode, "{$this->wflPrefix}:mailtemplate/@ref");
                $refs = $this->getNodeValues($nodeRefs);
                if ($refs) {
                    $workflow->setTransitionMailTemplates($transitionId, $refs);
                }

                $nodeRefs = $this->evaluate($transitionNode, "{$this->wflPrefix}:timer[@type='persistent']/@ref");
                $refs = $this->getNodeValues($nodeRefs);
                if ($refs) {
                    $workflow->setTransitionTimers($transitionId, $refs, WDocHooks::TIMER_PERSISTENT);
                }

                $nodeRefs = $this->evaluate($transitionNode, "{$this->wflPrefix}:timer[@type='volatile']/@ref");
                $refs = $this->getNodeValues($nodeRefs);
                if ($refs) {
                    $workflow->setTransitionTimers($transitionId, $refs, WDocHooks::TIMER_VOLATILE);
                }

                $nodeRefs = $this->evaluate($transitionNode, "{$this->wflPrefix}:timer[@type='unattach']/@ref");
                $refs = $this->getNodeValues($nodeRefs);
                if ($refs) {
                    $workflow->setTransitionTimers($transitionId, $refs, WDocHooks::TIMER_UNATTACH);
                }
            }

            $this->verboseMessages[]=sprintf("[%s] Record \"%s\"", $workflow->fromname, $workflow->name);
            $data = $this->getElementdata($workflow);
            return $data;
        }
        return [];
    }

    protected function getNodeValues(\DOMNodeList $nl)
    {
        $values = [];
        if ($nl->length > 0) {
            foreach ($nl as $node) {
                $values[] = $node->nodeValue;
            }
        }
        return $values;
    }

    /**
     * @param string      $name
     * @param \DOMElement $e
     *
     * @return \DOMNodeList
     */
    protected function getMailNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportWorkflowConfiguration::NSMTURL, $name);
    }

    protected function getTimerNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportWorkflowConfiguration::NSTMURL, $name);
    }

    protected function getWorkflowNodes(\DOMElement $e, $name)
    {
        return $e->getElementsByTagNameNS(ExportWorkflowConfiguration::NSWURL, $name);
    }
}
