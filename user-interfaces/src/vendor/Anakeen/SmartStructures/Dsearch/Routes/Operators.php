<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21/04/15
 * Time: 09:04
 */

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Core\Internal\SmartCollectionOperators;
use Anakeen\Router\ApiV2Response;

/**
 * Class Operators
 * @note    Used by route : GET /api/v2/smartstructures/dsearch/operators/
 * @package Anakeen\SmartStructures\Dsearch\Routes
 */
class Operators
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $etag = $this->getEtagInfo();
        $response = ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }
        $return = array();
        $arrayTypeArray = array(
            "text[]",
            "longtext[]",
            "image[]",
            "file[]",
            "enum[]",
            "date[]",
            "int[]",
            "double[]",
            "money[]",
            "password[]",
            "xml[]",
            "thesaurus[]",
            "time[]",
            "timestamp[]",
            "color[]",
            "docid[]",
            "htmltext[]",
            "account[]"
        );

        $operators=SmartCollectionOperators::getOperators();
        foreach ($operators as & $tmptop) {
            $tmpTypedLabel = array();
            $tmpTypedTitle = array();
            $tmpTypeArray = array();
            $tmpCompatibleTypes = isset($tmptop["type"]) ? $tmptop["type"] : null;
            $tmpId = array_search($tmptop, $operators);

            if (is_array($tmpCompatibleTypes)) {
                foreach ($tmpCompatibleTypes as $k => $type) {
                    if ($type == "array") {
                        unset($tmpCompatibleTypes[$k]);
                        $tmpCompatibleTypes = array_values($tmpCompatibleTypes);
                        $tmpCompatibleTypes = array_merge($tmpCompatibleTypes, $arrayTypeArray);
                    }
                }
            }

            if (!empty($tmptop["sdynlabel"])) {
                foreach ($tmptop["sdynlabel"] as $k => $label) {
                    $tmpTypedLabel[$k] = _($label);
                }
                foreach ($tmpTypedLabel as $type => $label) {
                    if ($type == "array") {
                        foreach ($arrayTypeArray as $k) {
                            $tmpTypeArray[$k] = $label;
                        }
                        unset($tmpTypedLabel[$type]);
                        $tmpTypedLabel = array_merge($tmpTypedLabel, $tmpTypeArray);
                    }
                }
            }
            $tmpTypeArray = array();

            if (!empty($tmptop["slabel"])) {
                foreach ($tmptop["slabel"] as $k => $label) {
                    $tmpTypedTitle[$k] = _($label);
                }
                foreach ($tmpTypedTitle as $type => $label) {
                    if ($type == "array") {
                        foreach ($arrayTypeArray as $k) {
                            $tmpTypeArray[$k] = $label;
                        }
                        unset($tmpTypedTitle[$type]);
                        $tmpTypedTitle = array_merge($tmpTypedTitle, $tmpTypeArray);
                    }
                }
            }

            if (($tmpId == "=") || ($tmpId == "!=")) {
                $tmpCompatibleTypes[] = "wid";
            }

            $return[] = array(
                "id" => $tmpId,
                "title" => _($tmptop["label"]),
                "label" => _($tmptop["dynlabel"]),
                "typedTitle" => $tmpTypedTitle,
                "typedLabel" => $tmpTypedLabel,
                "compatibleTypes" => $tmpCompatibleTypes,
                "operands" => $tmptop["operand"]
            );
        }

        return ApiV2Response::withData($response, $return);
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
