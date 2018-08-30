<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 21/08/18
 * Time: 14:55
 */

namespace Anakeen\Components\Grid\Routes;

use Anakeen\Router\ApiV2Response;

/**
 * Class Operators
 * @note    Used by route : GET /api/v2/smartstructures/dsearch/operators/
 * @package Anakeen\SmartStructures\Dsearch\Routes
 */
class Operators
{
    
    const FIELD_TYPES = [
        "text",
        "longtext",
        "image",
        "file",
        "enum",
        "date",
        "int",
        "double",
        "money",
        "password",
        "xml",
        "thesaurus",
        "time",
        "timestamp",
        "color",
        "docid",
        "htmltext",
        "account"
    ];

    const OPERATORS = [
        "ank:na" => [
            "label" => "N/A",
            "type" => [
                "text",
                "longtext",
                "htmltext",
                "date",
                "timestamp",
                "account",
                "docid",
                "enum",
                "int",
                "double",
                "file"
            ]

        ],
        "ank:empty" => [
            "label" => "is empty",
            "type" => [
                "text",
                "longtext",
                "htmltext",
                "date",
                "timestamp",
                "account",
                "docid",
                "enum",
                "int",
                "double",
                "file"
            ]
        ],
        "ank:not_empty" => [
            "label" => "is not empty",
            "type" => [
                "text",
                "longtext",
                "htmltext",
                "date",
                "timestamp",
                "account",
                "docid",
                "enum",
                "int",
                "double",
                "file"
            ]
        ],
        "ank:equal" => [

        ],
        "ank:inferior" => [

        ],
        "ank:superior" => [

        ],
        "ank:between" => [

        ],
        "ank:contains" => [
            "label" => "contains",
            "operator" => "~*",
            "type" => [
                "text",
                "longtext",
                "htmltext"
            ]
        ],
        "ank:not_contain" => [
            "label" => "does not contain",
            "type" => [
                "text",
                "longtext",
                "htmltext"
            ]
        ],
        "ank:one_contain" => [
            "label" => "one contain",
            "type" => [
                "text",
                "longtext",
                "htmltext"
            ]
        ],
        "ank:no_one_contain" => [
            "label" => "no one contain",
            "type" => [
                "text",
                "longtext",
                "htmltext"
            ]
        ],
        "ank:one_equal" => [

        ],
        "ank:no_one_equal" => [

        ],
        "ank:with" => [

        ],
        "ank:without" => [

        ]
    ];
    
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $etag = $this->getEtagInfo();
        $response = ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }

        return ApiV2Response::withData($response, $return);
    }

    protected function getTypes() {
        return self::FIELD_TYPES;
    }




    /**
     * Return etag info
     *
     * @return null|string
     */
    public function getEtagInfo()
    {
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");

        return implode(",", $result);
    }
}
