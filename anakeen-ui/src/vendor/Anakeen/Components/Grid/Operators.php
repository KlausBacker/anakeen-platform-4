<?php

namespace Anakeen\Components\Grid;

use Anakeen\Search\Filters\Contains;
use Anakeen\Search\Filters\ElementSearchFilter;
use Anakeen\Search\Filters\IsEmpty;
use Anakeen\Search\Filters\IsEqual;
use Anakeen\Search\Filters\IsGreater;
use Anakeen\Search\Filters\IsLesser;
use Anakeen\Search\Filters\IsNotEmpty;
use Anakeen\Search\Filters\IsNotEqual;
use Anakeen\Search\Filters\OneContains;
use Anakeen\Search\Filters\OneEquals;
use Anakeen\Search\Filters\OneGreaterThan;
use Anakeen\Search\Filters\OneLesserThan;
use Anakeen\Search\Filters\StartsWith;

/**
 * Class Operators
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

    public static function getSupportedOperators()
    {

        return [

            "contains" => [
                "label" => ___("Contains", "grid-component"),
                "operands" => [":field", ":value", StartsWith::NOCASE],
                "type" => [
                    "text",
                    "longtext",
                    "htmltext"
                ],
                "filterClass" => Contains::class
            ],
            "startswith" => [
                "label" => ___("Start with", "grid-component"),
                "operands" => [":field", ":value", StartsWith::NOCASE],
                "type" => [
                    "text",
                    "longtext",
                    "htmltext"
                ],
                "filterClass" => StartsWith::class
            ],
            "eq" => [
                "label" => ___("Is equal to", "grid-component"),
                "operands" => [":field", ":value"],
                "type" => [
                    "date",
                    "timestamp",
                    "time",
                    "int",
                    "double",
                    "money",
                    "enum",
                    "docid",
                    "account"
                ],
                "filterClass" => IsEqual::class
            ],


            "neq" => [
                "label" => ___("Is not equal to", "grid-component"),
                "operands" => [":field", ":value"],
                "type" => [
                    "date",
                    "timestamp",
                    "time",
                    "int",
                    "double",
                    "money",
                    "enum",
                    "docid",
                    "account"
                ],
                "filterClass" => IsNotEqual::class
            ],

            "eq*" => [
                "label" => ___("One of values equal to", "grid-component"),
                "operands" => [":field", ":value"],
                "type" => [
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]",
                    "enum[]",
                    "enum[][]",
                    "docid[]",
                    "account[]",
                    "docid[][]",
                    "account[][]"
                ],
                "filterClass" => OneEquals::class
            ],

            "neq*" => [
                "label" => ___("No one values are equal to", "grid-component"),
                "operands" => [":field", ":value", OneEquals::NOT],
                "type" => [
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]",
                    "enum[]",
                    "enum[][]",
                    "docid[]",
                    "account[]",
                    "docid[][]",
                    "account[][]",
                ],
                "filterClass" => OneEquals::class
            ],
            "gt" => [
                "label" => ___("Is greater than", "grid-component"),
                "operands" => [":field", ":value"],
                "type" => [
                    "date",
                    "timestamp",
                    "time",
                    "int",
                    "double",
                    "money"
                ],
                "filterClass" => IsGreater::class
            ],
            "gte" => [
                "label" => ___("Is greater than or equal to", "grid-component"),
                "operands" => [":field", ":value", IsGreater::EQUAL],
                "type" => [
                    "date",
                    "timestamp",
                    "time",
                    "int",
                    "double",
                    "money"
                ],
                "filterClass" => IsGreater::class
            ],


            "gt*" => [
                "label" => ___("One of values is greater than or equal to", "grid-component"),
                "operands" => [":field", ":value", OneGreaterThan::EQUAL],
                "type" => [
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]"
                ],
                "filterClass" => OneGreaterThan::class
            ],

            "gt**" => [
                "label" => ___("Any values is greater than or equal to", "grid-component"),
                "operands" => [":field", ":value", OneGreaterThan::EQUAL | OneGreaterThan::ALL],
                "type" => [
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]"
                ],
                "filterClass" => OneGreaterThan::class
            ],

            "lt" => [
                "label" => ___("Is lesser than", "grid-component"),
                "operands" => [":field", ":value"],
                "type" => [
                    "date",
                    "timestamp",
                    "time",
                    "int",
                    "double",
                    "money"
                ],
                "filterClass" => IsLesser::class
            ],
            "lte" => [
                "label" => ___("Is lesser than or equal to", "grid-component"),
                "operands" => [":field", ":value", IsLesser::EQUAL],
                "type" => [
                    "date",
                    "timestamp",
                    "time",
                    "int",
                    "double",
                    "money"
                ],
                "filterClass" => IsLesser::class
            ],


            "lt*" => [
                "label" => ___("One of values is lesser than or equal to", "grid-component"),
                "operands" => [":field", ":value", OneLesserThan::EQUAL],
                "type" => [
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]"
                ],
                "filterClass" => OneLesserThan::class
            ],

            "lt**" => [
                "label" => ___("Any values is lesser than or equal to", "grid-component"),
                "operands" => [":field", ":value", OneLesserThan::EQUAL | OneLesserThan::ALL],
                "type" => [
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]"
                ],
                "filterClass" => OneLesserThan::class
            ],

            "doesnotcontain" => [
                "label" => ___("Not contains", "grid-component"),
                "operands" => [":field", ":value", Contains::NOT | Contains::NOCASE],
                "type" => [
                    "text",
                    "longtext",
                    "htmltext"
                ],
                "filterClass" => Contains::class
            ],
            "contains*" => [
                "label" => ___("One text contains", "grid-component"),
                "operands" => [":field", ":value", OneContains::NOCASE],
                "type" => [
                    "text[]",
                    "longtext[]",
                    "htmltext[]"
                ],
                "filterClass" => OneContains::class
            ],
            "nocontains*" => [
                "label" => ___("At least one text not contains", "grid-component"),
                "operands" => [":field", ":value", OneContains::NOCASE] | OneContains::NOT,
                "type" => [
                    "text[]",
                    "longtext[]",
                    "htmltext[]"
                ],
                "filterClass" => OneContains::class
            ],

            "contains**" => [
                "label" => ___("All text contains", "grid-component"),
                "operands" => [":field", ":value", OneContains::NOCASE | OneContains::ALL],
                "type" => [
                    "text[]",
                    "longtext[]",
                    "htmltext[]"
                ],
                "filterClass" => OneContains::class
            ],
            "nocontains**" => [
                "label" => ___("No one text contains", "grid-component"),
                "operands" => [":field", ":value", OneContains::NOCASE | OneContains::NOT],
                "type" => [
                    "text[]",
                    "longtext[]",
                    "htmltext[]"
                ],
                "filterClass" => OneContains::class
            ],

            "isempty" => [
                "label" => ___("Is empty", "grid-component"),
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
                    "money",
                    "file",
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]",
                    "enum[]",
                    "enum[][]",
                    "docid[]",
                    "account[]",
                    "docid[][]",
                    "account[][]"
                ],
                "operands" => [":field"],
                "filterClass" => IsEmpty::class
            ],
            "isnotempty" => [
                "label" => ___("Is not empty", "grid-component"),
                "operands" => [":field"],
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
                    "money",
                    "file",
                    "int[]",
                    "double[]",
                    "money[]",
                    "date[]",
                    "timestamp[]",
                    "time[]",
                    "enum[]",
                    "enum[][]",
                    "docid[]",
                    "account[]",
                    "docid[][]",
                    "account[][]",
                ],
                "filterClass" => IsNotEmpty::class
            ],
        ];
    }

    public static function getOperator($operator)
    {
        $supportedOperators = self::getSupportedOperators();

        if (isset($supportedOperators[$operator])) {
            return $supportedOperators[$operator];
        }
        return null;
    }

    public static function getTypeOperators($type)
    {
        $supportedOperators = self::getSupportedOperators();
        $typeOperators = [];
        foreach ($supportedOperators as $k => $supportedOperator) {
            if (in_array($type, $supportedOperator["type"])) {
                $typeOperators[$k] = $supportedOperator;
            }
        }
        return $typeOperators;

    }


    /**
     * Return filter object that match kendo data source filter data
     * @param array $filterData
     * @return ElementSearchFilter|null
     */
    public static function getFilterObject(array $filterData)
    {
        $operator = self::getOperator($filterData["operator"]);

        if ($operator) {
            $class = $operator["filterClass"];
            $operands = $operator["operands"];
            $operandsValue = array_map(function ($item) use ($filterData) {
                if ($item && is_string($item) && $item[0] === ':') {
                    return $filterData[substr($item, 1)];
                } else {
                    return $item;
                }
            }, $operands);

            /**
             * @var ElementSearchFilter $filterObject
             */
            $filterObject = new $class(...$operandsValue);
            return $filterObject;

        }
        return null;
    }
}
