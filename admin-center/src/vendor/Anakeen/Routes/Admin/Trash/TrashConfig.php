<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\Routes\ColumnsConfig;
use Anakeen\Components\Grid\Routes\GridConfig;
use Anakeen\Router\ApiV2Response;

/**
 * Get Trash config
 *
 * @note Used by route : GET /api/v2/admin/trash/config/
 */

class TrashConfig extends GridConfig
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

    protected function getElementsConfig($originalConfig)
    {

        $originalConfig["toolbar"] = [];


        $titleConfig = ColumnsConfig::getColumnConfig("title");
        $titleConfig["title"] = "Title";

        $fromConfig = ColumnsConfig::getColumnConfig("fromid");
        $fromConfig["relation"] = "-1";
        $fromConfig["smartType"] = "text";
        $fromConfig["title"] = "Type";

        $dateConfig = ColumnsConfig::getColumnConfig("mdate");
        $dateConfig["title"] = "date of deletion";
        // $dateConfig["smartType"] = "datetimepicker";

        $authorConfig = ColumnsConfig::getColumnConfig("title");
        $authorConfig["title"] = "author of the deletion";

        // $titleConfig = ColumnsConfig::getColumnConfig("title");
        // $titleConfig["title"] = "Auteur de la suppression";


        $originalConfig["smartFields"] = [
            $titleConfig,
            $fromConfig,
            $dateConfig,
            $authorConfig
            // $authorConfig
        ];

        $originalConfig["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "display", "title" => "Display"],
                [ "action" => "restore", "title" => "Restore"],
                [ "action" => "delete", "title" => "Delete"]
            ]
        ];
        $originalConfig["footer"] = [];
        $originalConfig["contentURL"] = "/api/v2/admin/trash/content/";
        return $originalConfig;
    }
    protected function getConfig()
    {
        $config = parent::getConfig();
        return $this->getElementsConfig($config);
    }
}
