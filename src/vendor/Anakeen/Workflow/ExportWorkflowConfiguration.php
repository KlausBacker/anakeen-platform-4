<?php

namespace Anakeen\Workflow;

use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Ui\ExportRenderAccessConfiguration;

class ExportWorkflowConfiguration extends ExportRenderAccessConfiguration
{
    /**
     * @var WDocHooks
     */
    protected $workflow;
    const NSWURL = self::NSBASEURL . "workflow/1.0";
    const NSW = "workflow";
    const NSMTURL = self::NSBASEURL . "mailtemplate/1.0";
    const NSMT = "mail";
    const NSTMURL = self::NSBASEURL . "timer/1.0";
    const NSTM = "timer";

    const X_MAILTEMPLATEDATA = 1;
    const X_TIMERDATA = 2;
    const X_MAILTEMPLATEACCESS = 4;
    const X_TIMERACCESS = 8;
    const X_CONFIG = 16;
    const X_UIDATA = 32;
    const X_UIACCESS = 64;
    const X_UIDATAACCESS = 128;
    const X_UICONFIG = 256;
    const X_CONFIGACCESS = 512;
    const X_WFLACCESS = 1024;
    const X_ALL = -1;


    protected $dataSet = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(WDocHooks $wfl)
    {
        $this->workflow = $wfl;
        $this->sst = SEManager::getFamily($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_famid));

        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->domConfig = $this->cel("config");
        $this->domConfig->setAttribute("xmlns:" . self::NSW, self::NSWURL);
        $this->dom->appendChild($this->domConfig);
    }

    public function extractWorkflow($type)
    {
        if ($type & self::X_MAILTEMPLATEDATA) {
            // Mail Templates definition used in workflow
            $this->extractMailTemplatesData($this->domConfig);
        }

        if ($type & self::X_TIMERDATA) {
            // Timers definition used in workflow
            $this->extractTimersData($this->domConfig);
        }

        if ($type & self::X_CONFIG) {
            // References to mailtemplates and timers by steps
            $this->extractConfig($this->domConfig);
        }

        if ($type & self::X_UIDATA) {
            //  Ui : Cv and mask definition used in workflow
            $this->extractUiData($this->domConfig);
        }

        if ($type & self::X_UIDATAACCESS) {
            // Security (Ui) : Cv accesses and mask accesses used in workflow
            $this->extractUiDataAccesses($this->domConfig);
        }
        if ($type & self::X_UICONFIG) {
            // Ui : References to cv and mask by steps
            $this->extractUiConfig($this->domConfig);
        }
        if ($type & self::X_CONFIGACCESS) {
            // Security (Ref) : References to profil and field accesses by steps
            $this->extractAccessConfig($this->domConfig);
        }
        if ($type & self::X_WFLACCESS) {
            // Security (Workflow): Transition Accesses
            $this->extractAccess($this->domConfig);
        }

        if ($type & self::X_MAILTEMPLATEACCESS) {
            // Mail Templates accesses used in workflow
            $this->extractMailTemplatesDataAccess($this->domConfig);
        }

        if ($type & self::X_TIMERACCESS) {
            // Timers accesses used in workflow
            $this->extractTimersDataAccesses($this->domConfig);
        }

        if ($type & self::X_CONFIG) {
            // Reference default workflow on structure
            $this->extractDefaultWorkflow();
        }
    }

    protected function extractDefaultWorkflow()
    {
        if ($this->sst && $this->sst->wid == $this->workflow->id) {
            $structConfig = $this->cel("structure-configuration");
            $structConfig->setAttribute("name", $this->sst->name);
            if ($this->sst->id < 1000) {
                $structConfig->setAttribute("id", $this->sst->id);
            }
            $defaultWorkflowNode = $this->cel("default-workflow");
            $defaultWorkflowNode->setAttribute("ref", self::getLogicalName($this->workflow->id));
            $structConfig->appendChild($defaultWorkflowNode);

            $this->setComment("Default workflow for the structure");
            $this->domConfig->appendChild($structConfig);
        }
    }

    protected function extractAccess(\DOMElement $structConfig)
    {
        if ($this->workflow->profid) {
            $this->setComment("Access of workflow configuration", $structConfig);
            $accessControl = $this->setAccess($this->workflow->profid, "basic");
            $structConfig->appendChild($accessControl);
            $this->setComment("Access of workflow transitions", $structConfig);
            $accessControl = $this->setAccess($this->workflow->profid, "extended");
            $structConfig->appendChild($accessControl);
        }
    }

    protected function extractUiDataAccesses(\DOMElement $structConfig)
    {
        return $this->extractUiData($structConfig, "accesses");
    }

    protected function extractUiData(\DOMElement $structConfig, $extractPart = "config")
    {
        $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);
        $config = $this->celw("config");

        $config->setAttribute("name", self::getLogicalName($this->workflow->id));
        $config->setAttribute("structure", self::getLogicalName($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_famid)));

        if ($extractPart === "accesses") {
            $this->setComment("Accesses of elements (cvdoc, masks) used in workflow");
        } else {
            $this->setComment("Element (cvdoc, mask) data used in workflow");
        }

        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $stepNode = $this->celw("step");
            $stepNode->setAttribute("ref", $step);

            $maskId = $this->workflow->getStateMask($step);
            if ($maskId) {
                $mask = SEManager::getDocument($maskId);
                /** @var \SmartStructure\Mask $mask */
                $maskNode = null;
                if ($extractPart === "accesses") {
                    $this->setAccessProfile($mask);
                } else {
                    $maskNode = $this->extractMaskData($mask);
                }
                if ($maskNode) {
                    $structConfig->appendChild($maskNode);
                }
            }
            $cvId = $this->workflow->getStateViewControl($step);
            if ($cvId) {
                $cvdoc = SEManager::getDocument($cvId);
                /** @var \SmartStructure\Cvdoc $cvdoc */
                if ($extractPart === "accesses") {
                    $cvAccessNode = $this->extractCvdocDataAccess($cvdoc);
                    if ($cvAccessNode) {
                        $structConfig->appendChild($cvAccessNode);
                    }
                    $cvNode = $this->setAccess($cvdoc->id);
                } else {
                    $cvNode = $this->extractCvdocData($cvdoc);
                }
                if ($cvNode) {
                    $structConfig->appendChild($cvNode);
                }
            }
        }

        $structConfig->appendChild($config);
    }

    protected function extractUiConfig(\DOMElement $structConfig)
    {
        $config = $this->celw("config");

        $config->setAttribute("name", self::getLogicalName($this->workflow->id));
        $config->setAttribute("structure", self::getLogicalName($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_famid)));


        $stepsNode = $this->celw("steps");
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $stepNode = $this->celw("step");
            $stepNode->setAttribute("ref", $step);

            $color = $this->workflow->getColor($step);
            if ($color) {
                $colorNode = $this->celw("color");
                $colorNode->nodeValue = $color;
                $stepNode->appendChild($colorNode);
            }
            $stepsNode->appendChild($stepNode);

            $maskId = $this->workflow->getStateMask($step);
            if ($maskId) {
                $maskNode = $this->celw("mask");
                $maskNode->setAttribute("ref", static::getLogicalName($maskId));
                $stepNode->appendChild($maskNode);
            }
            $stepsNode->appendChild($stepNode);

            $cvId = $this->workflow->getStateViewControl($step);
            if ($cvId) {
                $cvNode = $this->celw("view-control");
                $cvNode->setAttribute("ref", static::getLogicalName($cvId));
                $stepNode->appendChild($cvNode);
            }
            $stepsNode->appendChild($stepNode);
        }

        $this->setComment("Elements (color, cvdoc, masks) user interface referenced in workflow");
        $config->appendChild($stepsNode);


        $structConfig->appendChild($config);
    }

    protected function extractAccessConfig(\DOMElement $structConfig)
    {
        $config = $this->celw("config");

        $config->setAttribute("name", self::getLogicalName($this->workflow->id));
        $config->setAttribute("structure", self::getLogicalName($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_famid)));


        $stepsNode = $this->celw("steps");
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $stepNode = $this->celw("step");
            $stepNode->setAttribute("ref", $step);

            $profilId = $this->workflow->getStateProfil($step);
            if ($profilId) {
                $timerNode = $this->celw("element-access-configuration");
                $timerNode->setAttribute("ref", static::getLogicalName($profilId));
                $stepNode->appendChild($timerNode);
            }
            $stepsNode->appendChild($stepNode);

            $fallId = $this->workflow->getStateFall($step);
            if ($fallId) {
                $timerNode = $this->celw("field-access-configuration");
                $timerNode->setAttribute("ref", static::getLogicalName($fallId));
                $stepNode->appendChild($timerNode);
            }
            $stepsNode->appendChild($stepNode);
        }

        $this->setComment("Step Element Accesses of workflow: profile and field accesses reference");
        $config->appendChild($stepsNode);


        $structConfig->appendChild($config);
    }

    protected function extractConfig(\DOMElement $structConfig)
    {
        $config = $this->celw("config");

        $config->setAttribute("name", self::getLogicalName($this->workflow->id));
        $config->setAttribute("label", $this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::ba_title));
        $config->setAttribute("structure", self::getLogicalName($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_famid)));

        $desc = $this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_desc);
        if ($desc) {
            $nodeDesc = $this->celw("description");
            $nodeDesc->appendChild($this->dom->createCDATASection($desc));
            $config->appendChild($nodeDesc);
        }
        $stepsNode = $this->celw("steps");
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $stepNode = $this->celw("step");
            $stepNode->setAttribute("ref", $step);
            $mails = $this->workflow->getStateMailTemplate($step);
            foreach ($mails as $mail) {
                $mailNode = $this->celw("mailtemplate");
                $mailNode->setAttribute("ref", static::getLogicalName($mail));
                $stepNode->appendChild($mailNode);
            }
            $timer = $this->workflow->getStateTimers($step);
            if ($timer) {
                $timerNode = $this->celw("timer");
                $timerNode->setAttribute("ref", static::getLogicalName($timer));
                $stepNode->appendChild($timerNode);
            }
            $stepsNode->appendChild($stepNode);
        }
        $config->appendChild($stepsNode);


        $transitionsNode = $this->celw("transitions");
        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $transitionNode = $this->celw("transition");
            $transitionNode->setAttribute("ref", $transitionName);
            $mails = $this->workflow->getTransitionMailTemplates($transitionName);
            foreach ($mails as $mail) {
                $mailNode = $this->celw("mailtemplate");
                $mailNode->setAttribute("ref", static::getLogicalName($mail));
                $transitionNode->appendChild($mailNode);
            }
            $timers = $this->workflow->getTransitionTimers($transitionName);

            foreach ($timers as $timer) {
                $timerNode = $this->celw("timer");
                $timerNode->setAttribute("ref", static::getLogicalName($timer["id"]));
                switch ($timer["type"]) {
                    case WDocHooks::TIMER_PERSISTENT:
                        $action = "persistent";
                        break;
                    case WDocHooks::TIMER_UNATTACH:
                        $action = "unattach";
                        break;
                    default:
                        $action = "volatile";
                }
                $timerNode->setAttribute("type", $action);
                $transitionNode->appendChild($timerNode);
            }

            $transitionsNode->appendChild($transitionNode);
        }
        $config->appendChild($transitionsNode);

        $this->setComment("Timer and Mail templates workflow references", $structConfig);
        $structConfig->appendChild($config);
    }


    protected function extractMailTemplatesData(\DOMElement $structConfig)
    {
        $this->setComment("Definition of mail templates used by workflow", $structConfig);
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $mails = $this->workflow->getStateMailTemplate($step);
            foreach ($mails as $mail) {
                if (empty($this->dataSet[$mail])) {
                    $structConfig->appendChild(ExportElementConfiguration::getMailTemplateData($mail, $this->dom));
                    $this->dataSet[$mail] = true;
                }
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $mails = $this->workflow->getTransitionMailTemplates($transitionName);
            foreach ($mails as $mail) {
                if (empty($this->dataSet[$mail])) {
                    $structConfig->appendChild(ExportElementConfiguration::getMailTemplateData($mail, $this->dom));
                    $this->dataSet[$mail] = true;
                }
            }
        }

        $this->domConfig->setAttribute("xmlns:" . self::NSMT, self::NSMTURL);
    }

    protected function extractMailTemplatesDataAccess(\DOMElement $structConfig)
    {
        $this->setComment("Accesses of mail templates used by workflow", $structConfig);
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $mails = $this->workflow->getStateMailTemplate($step);
            foreach ($mails as $mail) {
                $elt = SEManager::getDocument($mail);
                if ($elt) {
                    $this->setAccessProfile($elt);
                }
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $mails = $this->workflow->getTransitionMailTemplates($transitionName);
            foreach ($mails as $mail) {
                $elt = SEManager::getDocument($mail);
                if ($elt) {
                    $this->setAccessProfile($elt);
                }
            }
        }

        $this->domConfig->setAttribute("xmlns:" . self::NSMT, self::NSMTURL);
    }


    protected function extractTimersData(\DOMElement $structConfig)
    {
        $this->setComment("Definition of timers used by workflow", $structConfig);

        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $timer = $this->workflow->getStateTimers($step);
            if ($timer) {
                if (empty($this->dataSet[$timer])) {
                    $structConfig->appendChild(ExportElementConfiguration::getTimerData($timer, $this->dom));
                    $this->dataSet[$timer] = true;
                }
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $timers = $this->workflow->getTransitionTimers($transitionName);
            foreach ($timers as $timer) {
                if (empty($this->dataSet[$timer["id"]])) {
                    $structConfig->appendChild(ExportElementConfiguration::getTimerData($timer["id"], $this->dom));
                    $this->dataSet[$timer["id"]] = true;
                }
            }
        }
        $this->domConfig->setAttribute("xmlns:" . self::NSTM, self::NSTMURL);
    }


    protected function extractTimersDataAccesses(\DOMElement $structConfig)
    {
        $this->setComment("Accesses of timers used by workflow", $structConfig);

        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $timer = $this->workflow->getStateTimers($step);
            if ($timer) {
                $elt = SEManager::getDocument($timer);
                if ($elt) {
                    $this->setAccessProfile($elt);
                }
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $timers = $this->workflow->getTransitionTimers($transitionName);
            foreach ($timers as $timer) {
                $elt = SEManager::getDocument($timer["id"]);
                if ($elt) {
                    $this->setAccessProfile($elt);
                }
            }
        }
        $this->domConfig->setAttribute("xmlns:" . self::NSTM, self::NSTMURL);
    }


    protected function celw($name)
    {
        return $this->dom->createElementNS(self::NSWURL, self::NSW . ":" . $name);
    }

    protected function celmail($name)
    {
        return $this->dom->createElementNS(self::NSMTURL, self::NSMT . ":" . $name);
    }

    protected function celtimer($name)
    {
        return $this->dom->createElementNS(self::NSTMURL, self::NSTM . ":" . $name);
    }
}
