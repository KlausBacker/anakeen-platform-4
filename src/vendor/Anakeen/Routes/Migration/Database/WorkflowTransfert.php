<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Migration\Utils;
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
        $xmlGraphPath = $this->getWorkflowGraph($this->structureName);

        // Construct graph.xml

        parent::transfertConfig($this->structureName);

        // Delete auto created fields : They will be created on generate class
        $sql = sprintf("delete from docattr where docid=(select id from docfam where name ='%s') and options ~ 'autocreated=yes'", $this->structureName);
        DbManager::query($sql);

        $data["graph"] = $xmlGraphPath;
        $data["properties"]=$this->getProperties();
        return $data;
    }

    protected function getWorkflowGraph($workflowName)
    {
        $className = ucfirst(strtolower($workflowName));
        $graphData = Utils::wgetDynacase(sprintf("/api/v1/migration/4/workflows/%s/graph", urlencode($workflowName)));

        $template = file_get_contents(__DIR__ . '/../../../Migration/WorkflowGraph.xml.mustache');

        $vendorPath = sprintf("%s/vendor", ContextManager::getRootDirectory());
        $vendorName = ContextManager::getParameterValue("Migration", "VENDOR");
        if (!$vendorName) {
            throw new Exception("Migration VENDOR parameter is not set");
        }
        $namePath = [$vendorName, ConfigStructureTransfert::SMART_STRUCTURES, $className];
        $stubPath = sprintf("%s/%s/%s.graph.xml", $vendorPath, implode("/", $namePath), $className);

        $graphData["data"]["VENDOR"] = $vendorName;
        $mustache = new \Mustache_Engine();
        $routeConfigContent = $mustache->render($template, $graphData["data"]);
        Utils::writeFileContent($stubPath, $routeConfigContent);
        return $stubPath;
    }
}
