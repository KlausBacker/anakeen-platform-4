<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

class WorkflowTransfert
{
    protected $structureName;
    protected $structure;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters($args)
    {
        $this->structureName = $args["structure"];
        $this->structure = SEManager::getFamily($this->structureName);
        if (!$this->structure) {
            throw new Exception(sprintf("Structure \"%s\" not found", $this->structureName));
        }
    }

    protected function doRequest()
    {
        $data=[];

        // Transferring wdoc config => done by ConfigStructure Tranfert

        // Retrieve workflow graph data

        // Construct graph.xml
        // Construct Stub Workflow.php which include graph.xml

        // replace classname by stub

        // Regenerate class to redo workflow fields

        // Transferring wdoc data => done by DataElement Tranfert




        return $data;
    }
}
