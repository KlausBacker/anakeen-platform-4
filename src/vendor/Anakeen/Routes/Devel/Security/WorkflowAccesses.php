<?php

namespace Anakeen\Routes\Devel\Security;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

/**
 * Get all profile accesses references in a smart workflow object
 * use by route GET /api/v2/devel/config/smart/workflows/{workflow}.json
 */
class WorkflowAccesses
{
    /**
     * @var WDocHooks $workflow
     */
    protected $workflow;
    protected $workflowId = 0;
    protected $type = "structures";
    protected $complete=false;

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

        $this->complete = ($request->getQueryParam("complete") === "true");
    }

    public function doRequest()
    {
        $data["properties"] = $this->getProperties();
        $data["steps"] = $this->getStepConfig($this->complete);
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

    protected function getStepConfig($complete = false)
    {
        $steps = $this->workflow->getStates();
        $stepsData = [];
        foreach ($steps as $step) {
            $stepData = [];
            $stepData['id'] = $step;
            $stepData['label'] = $this->workflow->getStateLabel($step);
            $stepData['color'] = $this->workflow->getColor($step);

            $profid = $this->workflow->getStateProfil($step);
            $fallid = $this->workflow->getStateFall($step);

            if ($profid) {
                $profil = SEManager::getDocument($profid);
                if ($profil) {
                    $stepData['profil'] = ProfileUtils::getProperties($profil);
                    $stepData["profilAccess"] = ProfileUtils::getGreenAccesses($profil);
                }

                if ($complete === true) {
                     ProfileUtils::completeGroupAccess($stepData["profilAccess"]);
                      ProfileUtils::completeRoleAccess($stepData["profilAccess"]);
                       ProfileUtils::getGreyAccesses($stepData["profilAccess"], $profil);
                }
            }

            if ($fallid) {
                $fall = SEManager::getDocument($fallid);
                if ($fall) {
                    $stepData['fall'] = ProfileUtils::getProperties($fall);
                    $stepData["fallAccess"] = ProfileUtils::getGreenAccesses($fall);
                }
            }
            $stepsData[] = $stepData;
        }

        return $stepsData;
    }
}
