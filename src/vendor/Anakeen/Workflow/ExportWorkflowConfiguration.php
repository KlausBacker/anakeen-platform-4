<?php

namespace Anakeen\Workflow;

use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use SmartStructure\Fields\Mailtemplate as MailFields;

class ExportWorkflowConfiguration extends \Anakeen\Core\SmartStructure\ExportConfigurationAccesses
{
    /**
     * @var WDocHooks
     */
    protected $workflow;
    const NSWURL = self::NSBASEURL . "workflow/1.0";
    const NSW = "workflow";

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(WDocHooks $wfl)
    {
        $this->workflow = $wfl;
        $this->sst = SEManager::getFamily($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_fam));

        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->domConfig = $this->cel("config");
        $this->domConfig->setAttribute("xmlns:" . self::NSW, self::NSWURL);
        $this->dom->appendChild($this->domConfig);

        $structConfig = $this->cel("structure-configuration");
        $structConfig->setAttribute("name", $this->sst->name);
        if ($this->sst->id < 1000) {
            $structConfig->setAttribute("id", $this->sst->id);
        }

        $this->extract($structConfig);
    }

    protected function extract($structConfig)
    {
        $this->extractMailTemplatesData($this->domConfig);
        $this->extractAccess($this->domConfig);
        $this->extractConfig($this->domConfig);
    }

    protected function extractAccess(\DOMElement $structConfig)
    {
        if ($this->workflow->profid) {
            $accessControl = $this->setAccess($this->workflow->profid);

            $structConfig->appendChild($accessControl);
        }
    }

    protected function extractConfig(\DOMElement $structConfig)
    {
        $config = $this->celw("config");

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

        $structConfig->appendChild($config);
    }


    protected function extractMailTemplatesData(\DOMElement $structConfig)
    {
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            $mails = $this->workflow->getStateMailTemplate($step);
            foreach ($mails as $mail) {
                $structConfig->appendChild($this->getMailTemplateData($mail));
            }
        }

        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $mails = $this->workflow->getTransitionMailTemplates($transitionName);
            foreach ($mails as $mail) {
                $structConfig->appendChild($this->getMailTemplateData($mail));
            }
        }
    }

    protected function getMailTemplateData($name)
    {
        $mail = SEManager::getDocument($name);
        $mailNode = $this->celw("mailtemplate");

        $mailNode->setAttribute("name", static::getLogicalName($name));
        $mailNode->setAttribute("label", $mail->getRawValue(MailFields::tmail_title));

        $fromNode = $this->celw("from");

        $fromType = $mail->getMultipleRawValues(MailFields::tmail_fromtype);
        $from = $mail->getMultipleRawValues(MailFields::tmail_from);

        if ($from) {
            $fromNode->appendChild($this->getRecipient($fromType[0], $from[0]));
        }

        $nodeRecipients = $this->celw("recipients");
        $recips = $mail->getMultipleRawValues(MailFields::tmail_recip);
        $destTypes = $mail->getMultipleRawValues(MailFields::tmail_desttype);
        $copyModes = $mail->getMultipleRawValues(MailFields::tmail_copymode);
        foreach ($recips as $k => $recip) {
            $nodeRecipient = $this->celw("recipient");
            $nodeRecipient->setAttribute("dest", $copyModes[$k]);
            $recipient = $this->getRecipient($destTypes[$k], $recip);
            $nodeRecipient->appendChild($recipient);
            $nodeRecipients->appendChild($nodeRecipient);
        }
        $nodeSubject = $this->celw("subject");
        $nodeSubject->nodeValue = $mail->getRawValue(MailFields::tmail_subject);

        $nodeSave = $this->celw("savecopy");
        $nodeSave->nodeValue = ($mail->getRawValue(MailFields::tmail_savecopy) === "yes" ? "true" : "false");

        $nodeLink = $this->celw("savecopy");
        $nodeLink->nodeValue = ($mail->getRawValue(MailFields::tmail_ulink) === "yes" ? "true" : "false");


        $nodeBody = $this->celw("body");
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
            $attchementsNode = $this->celw("attachments");
            foreach ($attachements as $attachement) {
                $attchNode = $this->celw("attachment");
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

    protected function getRecipient($type, $value)
    {
        $label="";
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
                $node = $this->celw("address");
                $node->nodeValue = $value;
                break;
            case "A":
                $node = $this->celw("element-field-value");
                $node->nodeValue = $value;
                break;
            case "D":
                $node = $this->celw("element-account-field");
                $node->nodeValue = $value;
                break;
            case "E":
                $node = $this->celw("structure-parameter-value");
                $node->nodeValue = $value;
                break;
            case "DE":
                $node = $this->celw("structure-account-parameter");
                $node->nodeValue = $value;
                break;
            case "WA":
                $node = $this->celw("workflow-field-value");
                $node->nodeValue = $value;
                break;
            case "WE":
                $node = $this->celw("workflow-parameter-value");
                $node->nodeValue = $value;
                break;
            case "WD":
                $node = $this->celw("workflow-account-field");
                $node->nodeValue = $value;
                break;
            case "P":
                $node = $this->celw("config-parameter");

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
                $node = $this->celw("unknomtype");
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
}
