<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Dcp\Ui\RenderConfigManager;

/**
 * Class StructureTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route /api/v2/migration/database/transfert/structures/{structure}
 */
class UiStructureTransfert
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
        $data = [];


        $data["count"] = count($this->transfertRequest($this->structure));

        return $data;
    }

    protected static function transfertRequest(SmartStructure $structure)
    {

        $changes = [];
        Utils::importForeignTable("paramv");

        $sql = sprintf("select val from dynacase.paramv  where name='RENDER_PARAMETERS'");
        DbManager::query($sql, $renderParam, true, true);

        $renderParam = json_decode($renderParam, true);

        $className = ucfirst(strtolower($structure->name));
        $vendorName = ContextManager::getParameterValue("Migration", "VENDOR");
        $subDir = ContextManager::getParameterValue("Migration", "MODULE");
        if (!$vendorName) {
            throw new Exception("Migration VENDOR parameter is not set");
        }
        $vendorPath = sprintf("%s/vendor", ContextManager::getRootDirectory());
        if ($subDir) {
            $namePath = [$vendorName, $subDir, ConfigStructureTransfert::SMART_STRUCTURES, $className, "Ui"];
        } else {
            $namePath = [$vendorName, ConfigStructureTransfert::SMART_STRUCTURES, $className, "Ui"];
        }

        if (!empty($renderParam["families"][$structure->name])) {
            foreach ($renderParam["families"][$structure->name] as $kr => $vr) {
                switch ($kr) {
                    case "renderAccessClass":
                        $oriVr = $vr;
                        $accessClassName=sprintf("Access%s", $className);
                        $vr = sprintf("%s\\%s", implode("\\", $namePath), $accessClassName);
                        static::writeUiAccessStub(
                            $structure,
                            sprintf("%s/%s/%s.php", $vendorPath, implode("/", $namePath), $accessClassName),
                            ["Classname" => $accessClassName, "OriginalClass" => $oriVr, "Namespace"=>implode("\\", $namePath)]
                        );
                        break;

                    case "renderTransitionClass":
                        $oriVr = $vr;
                        $accessTransitionClassName=sprintf("Transition%s", $className);
                        $vr = sprintf("%s\\%s", implode("\\", $namePath), $accessTransitionClassName);
                        static::writeUiTransitionAccessStub(
                            $structure,
                            sprintf("%s/%s/%s.php", $vendorPath, implode("/", $namePath), $accessTransitionClassName),
                            ["Classname" => $accessTransitionClassName, "OriginalClass" => $oriVr, "Namespace"=>implode("\\", $namePath)]
                        );
                        break;
                }
                RenderConfigManager::setRenderParameter($structure->name, $kr, $vr);
            }
        }


        return $changes;
    }
    protected static function writeUiAccessStub($structure, $stubPath, $data)
    {
        $template = file_get_contents(__DIR__ . '/../../../Migration/RenderAccess.php.mustache');

        $data["structureName"] = $structure->name;
        $data["structureClass"] = ucfirst(strtolower($structure->name));
        /**
         * [
         * "Classname" => $className,
         * "Namespace" => implode("\\", $namePath),
         * "Extends" => $extends,
         * "OriginalClass" => $classPath,
         * "structureName" => $structureName
         * ]);
         */
        $mustache = new \Mustache_Engine();
        $stubBehaviorContent = $mustache->render($template, $data);
        Utils::writeFileContent($stubPath, $stubBehaviorContent);
        print "$stubPath\n";
    }
    protected static function writeUiTransitionAccessStub($structure, $stubPath, $data)
    {
        $template = file_get_contents(__DIR__ . '/../../../Migration/RenderTransitionAccess.php.mustache');

        $data["structureName"] = $structure->name;
        $data["structureClass"] = ucfirst(strtolower($structure->name));

        $mustache = new \Mustache_Engine();
        $stubBehaviorContent = $mustache->render($template, $data);
        Utils::writeFileContent($stubPath, $stubBehaviorContent);
        print "$stubPath\n";
    }
}
