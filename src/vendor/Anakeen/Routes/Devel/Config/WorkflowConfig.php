<?php

namespace Anakeen\Routes\Devel\Config;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Workflow\ExportWorkflowConfiguration as EWC;
/**
 * Class Structure
 * Get configuration of smart structure object
 * use by route GET /api/v2/devel/config/structures/{structure}.xml
 * use by route GET /api/v2/devel/config/uis/{structure}.xml
 * use by route GET /api/v2/devel/config/accesses/{structure}.xml
 */
class WorkflowConfig
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

        $response = $response->withAddedHeader("Content-type", "text/xml");
        $response = $response->write($this->doRequest());
        return $response;
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
        $this->type = $args["type"]??"all";
    }

    public function doRequest()
    {

        $e = new EWC($this->workflow);
        switch ($this->type) {
            case "uis":
                $e->extractWorkflow(EWC::X_UICONFIG|EWC::X_UIACCESS|EWC::X_UIDATA|EWC::X_UIDATAACCESS);
                break;
            case "data":
                $e->extractWorkflow(EWC::X_MAILTEMPLATEDATA|EWC::X_TIMERDATA);
                break;
            case "accesses":
                $e->extractWorkflow(EWC::X_CONFIGACCESS|EWC::X_WFLACCESS|X_TIMERACCESS|X_MAILTEMPLATEACCESS);
                break;
            case "config":
                $e->extractWorkflow(EWC::X_CONFIG);
                break;
            default:
                $e->extractWorkflow(EWC::X_ALL);
        }

        return $e->toXml();
    }
}
