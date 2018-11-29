<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\Routes\GridConfig;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Get Field Access grid config
 *
 * @note Used by route : GET api/v2/devel/security/fieldAccess/gridConfig
 */
class FieldAccessGridConfig extends GridConfig
{
    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $this->parseRequestParams($request, $response, $args);
        return ApiV2Response::withData($response, $this->getConfig());
    }

    protected function parseRequestParams(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $urlFieldsParam = $request->getQueryParam("fields", "");
        if (!empty($urlFieldsParam)) {
            $this->urlFields = array_map("trim", explode(",", $urlFieldsParam));
        }
    }

    protected static function getFilterable($type)
    {
        $operators = Operators::getTypeOperators($type);

        if (!$operators) {
            return false;
        }

        $stringsOperators=[];
        foreach ($operators as $k => $operator) {
            if (!empty($operator["typedLabels"])) {
                $stringsOperators[$k] = $operator["typedLabels"][$type] ?? $operator["label"];
            } else {
                $stringsOperators[$k] = $operator["label"];
            }
        }


        $filterable = [
            "operators" => [
                "string" => $stringsOperators,
                "date" => $stringsOperators,
            ],
            "cell" => [
                "enable" => true,
                "delay" => 9999999999 // Wait 115 days : only way to have the clear button easyly
            ]
        ];
        return $filterable;
    }

    protected function getFieldAccessConfig($originalConfig)
    {
        $dpdocConfig = ColumnsConfig::getColumnConfig("dpdoc_famid", SEManager::getFamily("FIELDACCESSLAYERLIST"));
        $dpdocConfig["smartType"] = "text";
        $dpdocConfig["title"] = "Dynamic";

        $structConfig = ColumnsConfig::getColumnConfig("fall_famid", SEManager::getFamily("FIELDACCESSLAYERLIST"));
        $structConfig["smartType"] = "text";

        $originalConfig["smartFields"] = [
            ColumnsConfig::getColumnConfig("title"),
            ColumnsConfig::getColumnConfig("name"),
            $structConfig,
            $dpdocConfig
        ];

        $originalConfig["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "rights", "title" => "View Rights"],
                [ "action" => "config", "title" => "View Config"]
            ]
        ];
        $originalConfig["contentURL"] = sprintf("/api/v2/devel/security/fieldAccess/");
        return $originalConfig;
    }
    protected function getConfig()
    {
        $config = parent::getConfig();
        return $this->getFieldAccessConfig($config);
    }
}
