<?php

namespace Anakeen\Workflow;

use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Ui\ExportRenderAccessConfiguration;
use SmartStructure\Fields\Mailtemplate as MailFields;
use SmartStructure\Fields\Timer as TimerFields;

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
                    $structConfig->appendChild($this->getMailTemplateData($mail));
                }
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $mails = $this->workflow->getTransitionMailTemplates($transitionName);
            foreach ($mails as $mail) {
                if (empty($this->dataSet[$mail])) {
                    $structConfig->appendChild($this->getMailTemplateData($mail));
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

    protected function getMailTemplateData($name)
    {
        $this->dataSet[$name] = true;
        $mail = SEManager::getDocument($name);
        $mailNode = $this->celmail("mailtemplate");

        $mailNode->setAttribute("name", static::getLogicalName($name));
        $mailNode->setAttribute("label", $mail->getRawValue(MailFields::tmail_title));
        $mailNode->setAttribute("structure", static::getLogicalName($mail->getRawValue(MailFields::tmail_family)));

        $fromNode = $this->celmail("from");

        $fromType = $mail->getMultipleRawValues(MailFields::tmail_fromtype);
        $from = $mail->getMultipleRawValues(MailFields::tmail_from);

        if ($from) {
            $fromNode->appendChild($this->getRecipient($fromType[0], $from[0]));
        }

        $nodeRecipients = $this->celmail("recipients");
        $recips = $mail->getMultipleRawValues(MailFields::tmail_recip);
        $destTypes = $mail->getMultipleRawValues(MailFields::tmail_desttype);
        $copyModes = $mail->getMultipleRawValues(MailFields::tmail_copymode);
        foreach ($recips as $k => $recip) {
            $nodeRecipient = $this->celmail("recipient");
            $nodeRecipient->setAttribute("dest", $copyModes[$k]);
            $recipient = $this->getRecipient($destTypes[$k], $recip);
            $nodeRecipient->appendChild($recipient);
            $nodeRecipients->appendChild($nodeRecipient);
        }
        $nodeSubject = $this->celmail("subject");
        $nodeSubject->nodeValue = $mail->getRawValue(MailFields::tmail_subject);

        $nodeSave = $this->celmail("savecopy");
        $nodeSave->nodeValue = ($mail->getRawValue(MailFields::tmail_savecopy) === "yes" ? "true" : "false");

        $nodeLink = $this->celmail("savecopy");
        $nodeLink->nodeValue = ($mail->getRawValue(MailFields::tmail_ulink) === "yes" ? "true" : "false");


        $nodeBody = $this->celmail("body");
        $nodeBody->setAttribute("content-type", "html");
        $nodeBody->appendChild($this->dom->createCDATASection($mail->getRawValue(MailFields::tmail_body)));

        $mailNode->appendChild($fromNode);
        $mailNode->appendChild($nodeRecipients);
        $mailNode->appendChild($nodeSubject);
        $mailNode->appendChild($nodeSave);
        $mailNode->appendChild($nodeLink);
        $mailNode->appendChild($nodeBody);

        $attachements = $mail->getMultipleRawValues(MailFields::tmail_attach);

        if ($attachements) {
            $attchementsNode = $this->celmail("attachments");
            foreach ($attachements as $attachement) {
                $attchNode = $this->celmail("attachment");
                if (preg_match("/([^(]*)\((.*)\)/", $attachement, $reg)) {
                    $value = trim($reg[1]);
                    $label = trim($reg[2]);
                    $attchNode->setAttribute("label", $label);
                    $attchNode->nodeValue = $value;
                } else {
                    $attchNode->nodeValue = $attachement;
                }
                $attchementsNode->appendChild($attchNode);
            }
            $mailNode->appendChild($attchementsNode);
        }


        return $mailNode;
    }


    protected function extractTimersData(\DOMElement $structConfig)
    {
        $this->setComment("Definition of timers used by workflow", $structConfig);

        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $timer = $this->workflow->getStateTimers($step);
            if ($timer) {
                if (empty($this->dataSet[$timer])) {
                    $structConfig->appendChild($this->getTimerData($timer));
                }
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $timers = $this->workflow->getTransitionTimers($transitionName);
            foreach ($timers as $timer) {
                if (empty($this->dataSet[$timer["id"]])) {
                    $structConfig->appendChild($this->getTimerData($timer["id"]));
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

    protected function getTimerData($name)
    {
        $this->dataSet[$name] = true;
        $timer = SEManager::getDocument($name);
        $timerNode = $this->celtimer("timer");

        $timerNode->setAttribute("name", static::getLogicalName($name));
        $timerNode->setAttribute("label", $timer->getRawValue(TimerFields::tm_title));

        $timerNode->setAttribute("structure", static::getLogicalName($timer->getRawValue(TimerFields::tm_family)));
        $timerNode->setAttribute("workflow", static::getLogicalName($timer->getRawValue(TimerFields::tm_workflow)));

        $dateRef = $timer->getRawValue(TimerFields::tm_dyndate);
        $dateNode = $this->celtimer("field-date-reference");
        if ($dateRef) {
            $dateNode->setAttribute("ref", $dateRef);
        }
        $timerNode->appendChild($dateNode);
        $deltaDay = $timer->getRawValue(TimerFields::tm_refdaydelta);
        $deltaHourDay = $timer->getRawValue(TimerFields::tm_refhourdelta);
        if ($deltaDay || $deltaHourDay) {
            $delay = sprintf("%d days %d hours", $deltaDay, $deltaHourDay);
            $dateNode->setAttribute("delta", $delay);
        }

        $tasks = $timer->getAttributeValue(TimerFields::tm_t_config);
        $tasksNode = $this->celtimer("tasks");

        /*  [1] => Array
        (
            [tm_delay] => 2
            [tm_hdelay] => 9
            [tm_iteration] => 1
            [tm_tmail] => Array
                (
                )

            [tm_state] => e_ccfd_sl_validee_directeur
            [tm_method] => ::sayHello(2)
        )*/

        foreach ($tasks as $task) {
            $taskNode = $this->celtimer("task");
            $delay = sprintf("%d days %d hours", $task[TimerFields::tm_delay], $task[TimerFields::tm_hdelay]);
            $taskNode->setAttribute("delta", $delay);
            if ($task[TimerFields::tm_state]) {
                $singleTask = $this->celtimer("setstate");
                $singleTask->setAttribute("state", $task[TimerFields::tm_state]);
                $taskNode->appendChild($singleTask);
            }
            if ($task[TimerFields::tm_tmail]) {
                foreach ($task[TimerFields::tm_tmail] as $mail) {
                    $singleTask = $this->celtimer("sendmail");
                    $singleTask->setAttribute("ref", self::getLogicalName($mail));
                    $taskNode->appendChild($singleTask);
                }
            }
            if ($task[TimerFields::tm_method]) {
                $singleTask = $this->celtimer("process");
                $method = new ParseFamilyMethod();
                $method->parse($task[TimerFields::tm_method]);

                $pcNode = $this->celtimer("process-callable");
                $pcNode->setAttribute("function", sprintf("%s::%s", $method->className, $method->methodName));
                $singleTask->appendChild($pcNode);

                foreach ($method->inputs as $input) {
                    $argNode = $this->celtimer("process-argument");
                    $argNode->nodeValue = $input->name;
                    $argNode->setAttribute("type", $input->type === "string" ? "string" : "field");
                    $singleTask->appendChild($argNode);
                }

                $taskNode->appendChild($singleTask);
            }
            $tasksNode->appendChild($taskNode);
        }
        $timerNode->appendChild($tasksNode);

        return $timerNode;
    }

    protected function getRecipient($type, $value)
    {
        $label = "";
        if (preg_match("/([^(]*)\((.*)\)/", $value, $reg)) {
            $value = trim($reg[1]);
            $label = trim($reg[2]);
        }
        switch ($type) {
            /*
                "F" :"Adresse fixe":
                "A" :"Attribut texte"
                "D" :"Attribut relation"
                "E" :"Paramètre de famille texte"
                "DE":"Paramètre de famille relation"
                "P" :"Paramètres globaux"
                "WA":"Attribut cycle"
                "WD":"Relation cycle"
                "WE":"Paramètre cycle"
            */
            case "F":
                $node = $this->celmail("address");
                $node->nodeValue = $value;
                break;
            case "A":
                $node = $this->celmail("element-field-value");
                $node->nodeValue = $value;
                break;
            case "D":
                $node = $this->celmail("element-account-field");
                $node->nodeValue = $value;
                break;
            case "E":
                $node = $this->celmail("structure-parameter-value");
                $node->nodeValue = $value;
                break;
            case "DE":
                $node = $this->celmail("structure-account-parameter");
                $node->nodeValue = $value;
                break;
            case "WA":
                $node = $this->celmail("workflow-field-value");
                $node->nodeValue = $value;
                break;
            case "WE":
                $node = $this->celmail("workflow-parameter-value");
                $node->nodeValue = $value;
                break;
            case "WD":
                $node = $this->celmail("workflow-account-field");
                $node->nodeValue = $value;
                break;
            case "P":
                $node = $this->celmail("config-parameter");

                if (strpos($value, '::') === false) {
                    $ns = ContextParameterManager::getNs($value);
                    $pvalue = $value;
                } else {
                    list($ns, $pvalue) = explode("::", $value);
                }
                $node->setAttribute("ns", $ns);
                $node->nodeValue = $pvalue;

                break;
            default:
                $node = $this->celmail("unknomtype");
                $node->setAttribute("type", $type);
        }
        if ($label) {
            $node->setAttribute("label", $label);
        }
        return $node;
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
