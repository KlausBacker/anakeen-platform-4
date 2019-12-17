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

    protected static function getAuthColumnConfig()
    {
        return [
            "field" => "auth",
            "smartType" => "text",
            "abstract" => true,
            "title" => "Authorization",
            "hidden" => true,
            "sortable" => false,
            "filterable" => false
        ];
    }

    protected static function getAuthorColumnConfig()
    {
        return [
            "field" => "author",
            "smartType" => "text",
            "abstract" => true,
            "title" => "Author of the deletion",
            "sortable" => true,
            "filterable" => self::getFilterable("text")
        ];
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
        $dateConfig["title"] = "Date of deletion";

        $authorConfig = self::getAuthorColumnConfig();

        // print_r($authorConfig)

        $authorization = self::getAuthColumnConfig();

        // $titleConfig = ColumnsConfig::getColumnConfig("title");
        // $titleConfig["title"] = "Auteur de la suppression";


        $originalConfig["smartFields"] = [
            $titleConfig,
            $fromConfig,
            $dateConfig,
            $authorConfig,
            $authorization
        ];

        $originalConfig["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                ["action" => "restore", "title" => "Restore"],
                ["action" => "display", "title" => "Display"],
                ["action" => "delete", "title" => "Delete from trash"],
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
