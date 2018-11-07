<?php

namespace Anakeen\Routes\Devel\Config;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

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
      //  $this->type = $args["type"];
    }

    public function doRequest()
    {

        $e = new \Anakeen\Workflow\ExportWorkflowConfiguration($this->workflow);

        return $e->toXml();
    }
}
