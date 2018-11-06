<?php

namespace Anakeen\Workflow;

use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

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

    protected function celw($name)
    {
        return $this->dom->createElementNS(self::NSWURL, self::NSW . ":" . $name);
    }
}
