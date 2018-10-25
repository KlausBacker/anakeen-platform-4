<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

class WorkflowTransfert extends ConfigStructureTransfert
{
    protected static function getBehaviorTemplateContent()
    {
        return file_get_contents(__DIR__ . '/../../../Migration/WorkflowBehavior.php.mustache');
    }

    protected function doRequest()
    {
        $data = [];

        // Transferring wdoc config => done by ConfigStructure Tranfert

        // Retrieve workflow graph data
        $this->getWorkflowGraph($this->structureName);

        // Construct graph.xml

        parent::transfertConfig($this->structureName);
        /**
         * Write PHP Class file
         */
        // Construct Stub Workflow.php which include graph.xml

        // replace classname by stub

        // Regenerate class to redo workflow fields

        // Transferring wdoc data => done by DataElement Tranfert


        return $data;
    }

    protected function getWorkflowGraph($workflowName)
    {
        $className=ucfirst(strtolower($workflowName));
        $graphData=Utils::wgetDynacase(sprintf("/api/v1/migration/4/workflows/%s/graph", urlencode($workflowName)));

        $template = file_get_contents(__DIR__ . '/../../../Migration/WorkflowGraph.xml.mustache');

        $vendorPath = sprintf("%s/vendor", ContextManager::getRootDirectory());
        $vendorName = ContextManager::getParameterValue("Migration", "VENDOR");
        if (!$vendorName) {
            throw new Exception("Migration VENDOR parameter is not set");
        }
        $namePath = [$vendorName, ConfigStructureTransfert::SMART_STRUCTURES, $className];
        $stubPath = sprintf("%s/%s/%s.graph.xml", $vendorPath, implode("/", $namePath), $className);

        $graphData["data"]["VENDOR"]=$vendorName;
        $mustache = new \Mustache_Engine();
        $routeConfigContent = $mustache->render($template, $graphData["data"]);
        Utils::writeFileContent($stubPath, $routeConfigContent);
        print "$stubPath\n";
    }
}
