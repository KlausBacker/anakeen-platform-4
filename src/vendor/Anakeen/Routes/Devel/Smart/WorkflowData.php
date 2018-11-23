<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\Devel\Security\ProfileUtils;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

/**
 * Get configuration of smart workflow object
 * use by route GET /api/v2/devel/config/smart/workflows/{workflow}.json
 */
class WorkflowData
{
    /**
     * @var WDocHooks $workflow
     */
    protected $workflow;
    protected $workflowId = 0;
    protected $type = "structures";

    /**
     * Return right accesses for a profil element
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->workflowId = $args["workflow"];
        $this->workflow = SEManager::getDocument($this->workflowId);
        if (!$this->workflow) {
            throw new Exception(sprintf("Workflow \"%s\" not found", $this->workflowId));
        }
        if (!is_a($this->workflow, WDocHooks::class)) {
            throw new Exception(sprintf("Element \"%s\" is not a workflow", $this->workflowId));
        }
    }

    public function doRequest()
    {
        $data["properties"] = $this->getProperties();
        $data["steps"] = $this->getStepConfig();
        $data["transitions"] = $this->getTransitionsConfig();
        return $data;
    }

    protected static function getElementRef($id)
    {
        if ($id) {
            $eData = SEManager::getRawDocument($id);
            if ($eData) {
                return $eData["name"] ?: $eData["id"];
            } else {
                return $id;
            }
        }
        return null;
    }

    protected function getProperties()
    {
        $data["id"] = $this->workflow->id;
        $data["name"] = $this->workflow->name;
        $data["structure"] = self::getElementRef($this->workflow->getRawValue(\SmartStructure\Fields\Wdoc::wf_famid));
        return $data;
    }

    protected function getStepConfig()
    {
        $steps = $this->workflow->getStates();
        $stepsData = [];
        foreach ($steps as $step) {
            $stepData = [];
            $stepData['id'] = $step;
            $stepData['label'] = $this->workflow->getStateLabel($step);
            $stepData['activity'] = $this->workflow->getActivity($step);
            $stepData['color'] = $this->workflow->getColor($step);
            $stepData['mask'] = self::getElementRef($this->workflow->getStateMask($step));
            $stepData['profil'] = self::getElementRef($this->workflow->getStateProfil($step));
            $stepData['viewcontrol'] = self::getElementRef($this->workflow->getStateViewControl($step));
            $stepData['fall'] = self::getElementRef($this->workflow->getStateFall($step));
            $stepData['timer'] = self::getElementRef($this->workflow->getStateTimer($step));

            $profile = SEManager::getDocument($stepData["profil"]);
            if (!empty($profile)) {
                $acls = $profile->acls ?: [];
                $extended = $profile->extendedAcls ?: [];
                foreach ($acls as $acl) {
                    if (isset($extended[$acl])) {
                        $isExtendedAcl = true;
                        $label = $extended[$acl]["description"];
                    } else {
                        $isExtendedAcl = false;
                        $label = DocumentAccess::$dacls[$acl]["description"];
                    }
                    $stepData["acls"][] = [
                        "name" => $acl,
                        "label" => $label,
                        "extended" => $isExtendedAcl

                    ];
                }
            }

            $mails = $this->workflow->getStateMailTemplate($step);
            $stepData["mailtemplates"] = [];
            foreach ($mails as $mail) {
                $stepData["mailtemplates"][] = self::getElementRef($mail);
            }
            $stepsData[] = $stepData;
        }

        return $stepsData;
    }

    protected function getTransitionsConfig()
    {
        $transitionsData = [];
        foreach ($this->workflow->transitions as $transitionName => $transitionConfig) {
            $transitionData = [];
            $transitionData["id"] = $transitionName;
            $transitionData["label"] = $this->workflow->getTransitionLabel($transitionName);
            $mails = $this->workflow->getTransitionMailTemplates($transitionName);
            $transitionData["mailtemplates"] = [];
            foreach ($mails as $mail) {
                $transitionData["mailtemplates"][] = self::getElementRef($mail);
            }
            $timers = $this->workflow->getTransitionTimers($transitionName);
            $transitionData["persistentTimers"] = $transitionData["unAttachTimers"] = $transitionData["volatileTimers"] = [];
            foreach ($timers as $timer) {
                switch ($timer["type"]) {
                    case WDocHooks::TIMER_PERSISTENT:
                        $transitionData["persistentTimers"][] = self::getElementRef($timer["id"]);
                        break;
                    case WDocHooks::TIMER_UNATTACH:
                        $transitionData["unAttachTimers"][] = self::getElementRef($timer["id"]);
                        break;
                    default:
                        $transitionData["volatileTimers"][] = self::getElementRef($timer["id"]);
                }
            }
            $transitionsData[] = $transitionData;
        }

        return $transitionsData;
    }
}
