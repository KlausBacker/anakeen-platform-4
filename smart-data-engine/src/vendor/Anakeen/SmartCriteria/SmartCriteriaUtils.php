<?php

namespace Anakeen\SmartCriteria;

use Anakeen\Search\Filters\Exception;

class SmartCriteriaUtils
{
    /**
     * @param $rawData array the json representing the value of the Search Criteria
     * @param bool $deep if true, will check deeply the object
     * @throws Exception
     */
    public static function checkRawData($rawData, $deep = true)
    {
        if (self::isMultidimensionalArray($rawData) && $deep) { //If not top level nor leaf level
            foreach ($rawData as $filterValue) {
                self::checkRawData($filterValue);
            }
        } else { //If top level or leaf level
            $kind = $rawData["kind"]??null;
            if (isset($kind) && in_array($kind, SmartCriteriaConfig::KIND_SUPPORTED_VALUES)) {
                $logic = $rawData["logic"];
                if (isset($logic) && in_array($logic, SmartCriteriaConfig::LOGIC_SUPPORTED_VALUES)) {
                    if ($kind !== SmartCriteriaConfig::KIND_FULLTEXT) {
                        $field = $rawData["field"];
                        if (empty($field)) {
                            throw new Exception("FLT0015", $kind);
                        }
                        $operator = $rawData["operator"];
                        if (!isset($operator)) {
                            throw new Exception("FLT0017", $kind);
                        } else {
                            if (!is_array($operator) || !isset($operator["key"]) || !isset($operator["filterMultiple"])) {
                                throw new Exception("FLT0021");
                            }
                        }
                    }
                    if (array_key_exists("filters", $rawData)) {
                        $filters = $rawData["filters"];
                        if (is_array($filters) && count($filters) > 0 && $deep) {
                            self::checkRawData($rawData["filters"]);
                        }
                    }
                } else {
                    throw new Exception("FLT0014", $logic);
                }
            } else {
                throw new Exception("FLT0013", $kind);
            }
        }
    }

    public static function isMultidimensionalArray($object)
    {
        foreach ($object as $entry) {
            if (!is_array($entry)) {
                return false;
            }
        }
        return true;
    }

    public static function getCriteriaKind($seType)
    {
        $criteriaType = $seType;
        switch ($seType) {
            case "htmltext":
            case "password":
            case "json":
            case "xml":
            case "longtext":
            case "text":
                $criteriaType = SmartCriteriaConfig::TEXTUAL_KIND;
                break;
            case "account":
            case "docid":
                $criteriaType = SmartCriteriaConfig::RELATION_KIND;
                break;
            case "enum":
                $criteriaType = SmartCriteriaConfig::ENUM_KIND;
                break;
            case SmartCriteriaConfig::FILE_KIND:
            case "image":
                $criteriaType = SmartCriteriaConfig::FILE_KIND;
                break;
            case "timestamp":
            case "time":
            case "date":
                $criteriaType = SmartCriteriaConfig::TEMPORAL_KIND;
                break;
            case "int":
            case "double":
            case "money":
            case "integer":
                $criteriaType = SmartCriteriaConfig::NUMERICAL_KIND;
                break;
            case "title":
                $criteriaType = SmartCriteriaConfig::TITLE_KIND;
                break;
            case "state":
                $criteriaType = SmartCriteriaConfig::STATE_KIND;
                break;
            default:
                break;
        }
        return $criteriaType;
    }

    public static function areOperatorsEqual(&$op1, &$op2)
    {
        if (array_key_exists("key", $op1) && array_key_exists("key", $op2) && $op1["key"] === $op2["key"]) {
            if (!array_key_exists("options", $op1)) {
                $op1["options"] = array();
            }
            if (!array_key_exists("options", $op2)) {
                $op1["options"] = array();
            }
            if (count($op1Options = $op1["options"]) === count($op2Options = $op2["options"])) {
                $optionIntersection = array_filter($op1Options, function ($op) use ($op2Options) {
                    return !in_array($op, $op2Options);
                });
                if (empty($optionIntersection)) {
                    return true;
                }
            }
        }
        return false;
    }
}
