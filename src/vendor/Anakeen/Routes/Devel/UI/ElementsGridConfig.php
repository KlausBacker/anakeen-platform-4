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

    protected function getElementsConfig($originalConfig)
    {
        $originalConfig["toolbar"] = null;


        $titleConfig = ColumnsConfig::getColumnConfig("title");
        $titleConfig["title"] = "Title";

        $nameConfig = ColumnsConfig::getColumnConfig("name");
        $nameConfig["title"] = "Logical Name";

        $fromConfig = ColumnsConfig::getColumnConfig("fromid");
        $fromConfig["relation"] = "-1";
        $fromConfig["smartType"] = "text";
        $fromConfig["title"] = "Type";

        $idConfig = ColumnsConfig::getColumnConfig("initid");
        $idConfig["filterable"] = self::getFilterable("int");
        $idConfig["title"] = "InitId";
        $idConfig["smartType"] = "integer";

        $docTypeConfig = ColumnsConfig::getColumnConfig("doctype");
        $docTypeConfig["hidden"] = true;

        $profilIdConfig = ColumnsConfig::getColumnConfig("profid");
        $profilIdConfig["hidden"] = true;

        $originalConfig["smartFields"] = [
            $titleConfig,
            $nameConfig,
            $idConfig,
            $fromConfig,
            $docTypeConfig,
            $profilIdConfig
        ];

        $originalConfig["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "consult", "title" => "Consult"],
                [ "action" => "viewJSON", "title" => "JSON"],
                [ "action" => "viewXML", "title" => "XML"],
                [ "action" => "viewProps", "title" => "Properties"],
                [ "action" => "security", "title" => "Security"]
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
