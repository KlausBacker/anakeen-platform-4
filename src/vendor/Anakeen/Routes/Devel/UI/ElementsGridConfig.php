<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\Routes\GridConfig;
use Anakeen\Router\ApiV2Response;

class ElementsGridConfig extends GridConfig
{

    protected $vendorType = "all";

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
        $this->vendorType = $request->getQueryParam("vendor", "all");
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

    protected static function getFamilyPropConfig()
    {
        return [
            "field" => "family",
            "smartType" => "text",
            "title" => "Parent Structure",
            "property" => true,
            "sortable" => false,
            "filterable" => self::getFilterable("text")
        ];
    }

    protected function getElementsConfig($originalConfig)
    {
        $originalConfig["toolbar"] = null;
        $originalConfig["smartFields"] = [
            static::getFamilyPropConfig(),
            ColumnsConfig::getColumnConfig("title"),
            ColumnsConfig::getColumnConfig("id"),
            ColumnsConfig::getColumnConfig("name"),
        ];

        $originalConfig["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "consult", "title" => "Consult"],
                [ "action" => "viewJSON", "title" => "View JSON"],
                [ "action" => "viewXML", "title" => "View XML"],
                [ "action" => "security", "title" => "View Security"],
            ]
        ];
        $originalConfig["footer"] = [];
        $originalConfig["contentURL"] = sprintf("/api/v2/devel/security/elements/?vendor=%s", $this->vendorType);
        return $originalConfig;
    }
    protected function getConfig()
    {
        $config = parent::getConfig();
        return $this->getElementsConfig($config);
    }
}
