<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\Routes\GridConfig;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

class HubStationAdminConfig extends GridConfig
{
    protected $structureName = "";

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $this->parseRequestParams($request, $response, $args);
        return ApiV2Response::withData($response, $this->getConfig());
    }

    protected function parseRequestParams(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $this->structureName = $args["hubId"];
    }

    protected static function getFilterable($type)
    {
        $operators = Operators::getTypeOperators($type);

        if (!$operators) {
            return false;
        }

        $stringsOperators = [];
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

    protected function getAdminConfig($originalConfig)
    {
        $originalConfig["toolbar"] = [
            "title" => "",
            "actionConfigs" => [
                ["action" => "create", "title" => "Create"]
            ]
        ];
        $originalConfig["smartFields"] = [
            ColumnsConfig::getColumnConfig("hub_docker_position", SEManager::getDocument("HUBCONFIGURATION")),
            ColumnsConfig::getColumnConfig("hub_order", SEManager::getDocument("HUBCONFIGURATION")),
            ColumnsConfig::getColumnConfig("hub_final_icon", SEManager::getDocument("HUBCONFIGURATION")),
            ColumnsConfig::getColumnConfig("hub_title", SEManager::getDocument("HUBCONFIGURATION"))
        ];

        $originalConfig["actions"] = [
            "title" => "",
            "actionConfigs" => [
                ["action" => "consult", "title" => "Consult"],
                ["action" => "edit", "title" => "Edit"]
            ]
        ];
        $originalConfig["footer"] = [];
        $originalConfig["contentURL"] = sprintf("/api/v2/hub/station/" . $this->structureName . "/admin/");
        return $originalConfig;
    }

    protected function getConfig()
    {
        $config = parent::getConfig();
        return $this->getAdminConfig($config);
    }
}
