<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\Routes\GridConfig;
use Anakeen\Router\ApiV2Response;

class ProfilesGridConfig extends GridConfig
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

    protected static function getFamilyPropConfig()
    {
        return [
            "field" => "family",
            "smartType" => "text",
            "title" => "Type",
            "property" => true,
            "sortable" => true,
            "filterable" => self::getFilterable("text")
        ];
    }

    protected static function getDynamciFamidConfig()
    {
        return [
            "field" => "dpdoc_famid",
            "smartType" => "text",
            "title" => "Dynamic",
            "sortable" => true,
            "filterable" => self::getFilterable("text")
        ];
    }

    protected function getProfilesConfig($originalConfig)
    {
        $originalConfig["toolbar"] = [];
        $originalConfig["smartFields"] = [
            ColumnsConfig::getColumnConfig("title"),
            ColumnsConfig::getColumnConfig("name"),
            static::getFamilyPropConfig(),
            static::getDynamciFamidConfig()
        ];

        $originalConfig["actions"] = [];
        $originalConfig["footer"] = [];
        $originalConfig["contentURL"] = sprintf("/api/v2/devel/security/profiles/");
        return $originalConfig;
    }
    protected function getConfig()
    {
        $config = parent::getConfig();
        return $this->getProfilesConfig($config);
    }
}