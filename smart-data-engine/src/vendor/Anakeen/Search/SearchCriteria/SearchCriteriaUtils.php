<?php

namespace Anakeen\Search\SearchCriteria;

use Anakeen\Search\Filters\Exception;
use Anakeen\Search\Filters\NAFilter;
use String\sprintf;

class SearchCriteriaUtils
{
    const KIND_FIELD = "field";
    const KIND_PROPERTY = "property";
    const KIND_VIRTUAL = "virtual";
    const KIND_FULLTEXT = "fulltext";
    const KIND_SUPPORTED_VALUES = [self::KIND_FIELD, self::KIND_PROPERTY, self::KIND_VIRTUAL, self::KIND_FULLTEXT];

    // kinds of smart types
    const TEXTUAL_KIND = "textual";
    const TEMPORAL_KIND = "temporal";
    const NUMERICAL_KIND = "numerical";
    const RELATION_KIND = "relation";
    const FILE_KIND = "file";
    const ENUM_KIND = "enum";
    const STATE_KIND = "state";
    const TITLE_KIND = "title";

    const LOGIC_AND = "and";
    const LOGIC_OR = "or";
    const LOGIC_SUPPORTED_VALUES = [self::LOGIC_AND, self::LOGIC_OR];

    const PROPERTY_TITLE = "title";
    const PROPERTY_STATE = self::STATE_KIND;
    const PROPERTY_SUPPORTED_VALUES = [self::PROPERTY_TITLE, self::PROPERTY_STATE];

    private const FIELD = ':field';
    private const VALUE = ':value';
    private const SIMPLE_FIELD = "simpleField";
    private const MULTIPLE_FIELD = "multipleField";
    private const SIMPLE_FILTER = "simpleFilter";
    private const MULTIPLE_FILTER = "multipleFilter";

    private const BETWEEN_OPERATORS = ["between", "oneBetween"];

    private static function getFilterMap()
    {
        return [
        self::TEXTUAL_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "contains",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Textual simple"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Textual\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Textual simple"),
                            \Anakeen\Search\Filters\Textual\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Textual simple"),
                        ],
                    ],
                    "contains" => [
                        "class" => \Anakeen\Search\Filters\Textual\Contains::class,
                        "operands" => [self::FIELD, self::VALUE, \Anakeen\Search\Filters\Textual\Contains::NOCASE + \Anakeen\Search\Filters\Textual\Contains::NODIACRITIC],
                        "availableOptions" => [
                            \Anakeen\Search\Filters\Textual\Contains::NOCASE,
                            \Anakeen\Search\Filters\Textual\Contains::NODIACRITIC,
                        ],
                        "labels" => [
                            0 => ___("Contains", "SearchCriteriaUtils.Textual simple"),
                            \Anakeen\Search\Filters\Textual\Contains::NOT => ___("Does not contain", "SearchCriteriaUtils.Textual simple"),
                            \Anakeen\Search\Filters\Textual\Contains::STARTSWITH => ___("Starts with", "SearchCriteriaUtils.Textual simple"),
                            \Anakeen\Search\Filters\Textual\Contains::STARTSWITH + \Anakeen\Search\Filters\Textual\Contains::NOT =>
                            ___("Does not start with", "SearchCriteriaUtils.Textual simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => []
            ],
            self::MULTIPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "oneContains",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Textual multi"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Textual\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtilsTextual multi"),
                            \Anakeen\Search\Filters\Textual\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtilsTextual multi"),
                        ],
                    ],
                    "oneContains" => [
                        "class" => \Anakeen\Search\Filters\Textual\OneContains::class,
                        "operands" => [self::FIELD, self::VALUE, \Anakeen\Search\Filters\Textual\OneContains::NOCASE + \Anakeen\Search\Filters\Textual\OneContains::NODIACRITIC],
                        "availableOptions" => [
                            \Anakeen\Search\Filters\Textual\OneContains::NOCASE,
                            \Anakeen\Search\Filters\Textual\OneContains::NODIACRITIC,
                        ],
                        "labels" => [
                            0 => ___("One contains", "SearchCriteriaUtilsTextual multi"),
                            \Anakeen\Search\Filters\Textual\OneContains::NOT => ___("One does not contain", "SearchCriteriaUtilsTextual multi"),
                            \Anakeen\Search\Filters\Textual\OneContains::ALL => ___("All contain", "SearchCriteriaUtilsTextual multi"),
                            \Anakeen\Search\Filters\Textual\OneContains::NOT + \Anakeen\Search\Filters\Textual\OneContains::ALL =>
                            ___("None contain", "SearchCriteriaUtilsTextual multi"),
                        ],
                    ]
                ],
                self::MULTIPLE_FILTER => []
            ]
        ],
        self::TEMPORAL_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "between",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Temporal simple"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Temporal\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Temporal simple"),
                            \Anakeen\Search\Filters\Temporal\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Temporal simple"),
                        ],
                    ],
                    "between" => [
                        "class" => \Anakeen\Search\Filters\Temporal\Between::class,
                        "operands" => [self::FIELD, self::VALUE, \Anakeen\Search\Filters\Temporal\Between::EQUALRIGHT + \Anakeen\Search\Filters\Temporal\Between::EQUALLEFT],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is between", "SearchCriteriaUtils.Temporal simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => []
            ],
            self::MULTIPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "oneBetween",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Temporal multi"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Temporal\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Temporal multi"),
                            \Anakeen\Search\Filters\Temporal\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Temporal multi"),
                        ],
                    ],
                    "oneBetween" => [
                        "class" => \Anakeen\Search\Filters\Temporal\OneBetween::class,
                        "operands" => [self::FIELD, self::VALUE, 0],
                        "availableOptions" => [
                            \Anakeen\Search\Filters\Temporal\OneBetween::LEFTEQUAL,
                            \Anakeen\Search\Filters\Temporal\OneBetween::RIGHTEQUAL
                        ],
                        "labels" => [
                            0 => ___("One date is between", "SearchCriteriaUtils.Temporal multi"),
                            \Anakeen\Search\Filters\Temporal\OneBetween::NOT => ___("One date is not between", "SearchCriteriaUtils.Temporal multi"),
                            \Anakeen\Search\Filters\Temporal\OneBetween::ALL => ___("All dates are between", "SearchCriteriaUtils.Temporal multi"),
                            \Anakeen\Search\Filters\Temporal\OneBetween::ALL + \Anakeen\Search\Filters\Temporal\OneBetween::NOT =>
                            ___("No dates are between", "SearchCriteriaUtils.Temporal multi"),
                        ],
                    ]
                ],
                self::MULTIPLE_FILTER => []
            ]
        ],
        self::NUMERICAL_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "between",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Numerical simple"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Numerical\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Numerical simple"),
                            \Anakeen\Search\Filters\Numerical\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Numerical simple"),
                        ],
                    ],
                    "between" => [
                        "class" => \Anakeen\Search\Filters\Numerical\Between::class,
                        "operands" => [self::FIELD, self::VALUE, \Anakeen\Search\Filters\Numerical\Between::EQUALRIGHT + \Anakeen\Search\Filters\Numerical\Between::EQUALLEFT],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is between", "SearchCriteriaUtils.Numerical simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => []
            ],
            self::MULTIPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "oneBetween",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Numerical multi"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Numerical\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Numerical multi"),
                            \Anakeen\Search\Filters\Numerical\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Numerical multi"),
                        ],
                    ],
                    "oneBetween" => [
                        "class" => \Anakeen\Search\Filters\Numerical\OneBetween::class,
                        "operands" => [self::FIELD, self::VALUE,
                            \Anakeen\Search\Filters\Numerical\OneBetween::LEFTEQUAL + \Anakeen\Search\Filters\Numerical\OneBetween::RIGHTEQUAL],
                        "availableOptions" => [
                            \Anakeen\Search\Filters\Numerical\OneBetween::LEFTEQUAL,
                            \Anakeen\Search\Filters\Numerical\OneBetween::RIGHTEQUAL
                        ],
                        "labels" => [
                            0 => ___("One value is between", "SearchCriteriaUtils.Numerical multi"),
                            \Anakeen\Search\Filters\Numerical\OneBetween::NOT => ___("One value is not between", "SearchCriteriaUtils.Numerical multi"),
                            \Anakeen\Search\Filters\Numerical\OneBetween::ALL => ___("All values are between", "SearchCriteriaUtils.Numerical multi"),
                            \Anakeen\Search\Filters\Numerical\OneBetween::ALL + \Anakeen\Search\Filters\Numerical\OneBetween::NOT =>
                            ___("No values are between", "SearchCriteriaUtils.Numerical multi"),
                        ],
                    ]
                ],
                self::MULTIPLE_FILTER => []
            ]
        ],
        self::RELATION_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "equalsOne",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Relations simple"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Relation\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Relations simple"),
                            \Anakeen\Search\Filters\Relation\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Relations simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => [
                    "equalsOne" => [
                        "class" => \Anakeen\Search\Filters\Relation\EqualsOne::class,
                        "operands" => [self::FIELD, self::VALUE, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is among", "SearchCriteriaUtils.Relations simple"),
                            \Anakeen\Search\Filters\Relation\EqualsOne::NOT => ___("Is not among", "SearchCriteriaUtils.Relations simple"),
                        ],
                    ],
                ]
            ],
            self::MULTIPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "oneEqualsMulti",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Relations multi"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Relation\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Relations multi"),
                            \Anakeen\Search\Filters\Relation\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Relations multi"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => [
                    "oneEqualsMulti" => [
                        "class" => \Anakeen\Search\Filters\Relation\OneEqualsMulti::class,
                        "operands" => [self::FIELD, self::VALUE, 0],
                        "availableOptions" => [],
                        "dynamicLabel" => true,
                        "labels" => [
                            0 => ___("One of the '%s' is among", "SearchCriteriaUtils.Relations multi"),
                            \Anakeen\Search\Filters\Relation\OneEqualsMulti::NOT => ___("One of the '%s' is not among", "SearchCriteriaUtils.Relations multi"),
                            \Anakeen\Search\Filters\Relation\OneEqualsMulti::ALL => ___("All of the '%s' are among", "SearchCriteriaUtils.Relations multi"),
                            \Anakeen\Search\Filters\Relation\OneEqualsMulti::ALL + \Anakeen\Search\Filters\Relation\OneEqualsMulti::NOT =>
                            ___("None of the '%s' are among", "SearchCriteriaUtils.Relations multi"),
                        ],
                    ]
                ]
            ]
        ],
        self::ENUM_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "equalsOne",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Enum simple"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Enum\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Enum simple"),
                            \Anakeen\Search\Filters\Enum\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Enum simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => [
                    "equalsOne" => [
                        "class" => \Anakeen\Search\Filters\Enum\EqualsOne::class,
                        "operands" => [self::FIELD, self::VALUE, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is among", "SearchCriteriaUtils.Enum simple"),
                            \Anakeen\Search\Filters\Enum\EqualsOne::NOT => ___("Is not among", "SearchCriteriaUtils.Enum simple"),
                        ],
                    ],
                ]
            ],
            self::MULTIPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "oneEqualsMulti",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Enum multi"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Enum\IsEmpty::class,
                        "operands" => [self::FIELD, 0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Enum multi"),
                            \Anakeen\Search\Filters\Relation\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Enum multi"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => [
                    "oneEqualsMulti" => [
                        "class" => \Anakeen\Search\Filters\Relation\OneEqualsMulti::class,
                        "operands" => [self::FIELD, self::VALUE, 0],
                        "availableOptions" => [],
                        "dynamicLabel" => true,
                        "labels" => [
                            0 => ___("One of the '%s' is among", "SearchCriteriaUtils.Enum multi"),
                            \Anakeen\Search\Filters\Enum\OneEqualsMulti::NOT => ___("One of the '%s' is not among", "SearchCriteriaUtils.Enum multi"),
                            \Anakeen\Search\Filters\Enum\OneEqualsMulti::ALL => ___("All of the '%s' are among", "SearchCriteriaUtils.Enum multi"),
                            \Anakeen\Search\Filters\Enum\OneEqualsMulti::ALL + \Anakeen\Search\Filters\Enum\OneEqualsMulti::NOT =>
                            ___("None of the '%s' are among", "SearchCriteriaUtils.Enum multi"),
                        ],
                    ]
                ]
            ]
        ],
        self::STATE_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "none",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.State simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => [
//                    "equalsOne" => [
//                        "class" => \Anakeen\Search\Filters\State\EqualsOne::class,
//                        "operands" => [self::FIELD, self::VALUE, 0],
//                        "availableOptions" => [],
//                        "labels" => [
//                            0 => ___("Is among", "SearchCriteriaUtils.State simple"),
//                            \Anakeen\Search\Filters\State\EqualsOne::NOT => ___("Is not among", "SearchCriteriaUtils.State simple"),
//                        ],
//                    ],
                ]
            ],
        ],
        self::TITLE_KIND => [
            self::SIMPLE_FIELD => [
                "defaultOperator" => [
                    "key" => "contains",
                    "options" => [],
                ],
                self::SIMPLE_FILTER => [
                    "none" => [
                        "class" => NAFilter::class,
                        "operands" => [self::FIELD],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("NA", "SearchCriteriaUtils.Title simple"),
                        ],
                    ],
                    "isEmpty" => [
                        "class" => \Anakeen\Search\Filters\Title\IsEmpty::class,
                        "operands" => [0],
                        "availableOptions" => [],
                        "labels" => [
                            0 => ___("Is empty", "SearchCriteriaUtils.Title simple"),
                            \Anakeen\Search\Filters\Title\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Title simple"),
                        ],
                    ],
                    "contains" => [
                        "class" => \Anakeen\Search\Filters\Title\Contains::class,
                        "operands" => [self::FIELD, self::VALUE,
                            \Anakeen\Search\Filters\Title\Contains::NOCASE + \Anakeen\Search\Filters\Title\Contains::NODIACRITIC],
                        "availableOptions" => [
                            \Anakeen\Search\Filters\Title\Contains::NOCASE,
                            \Anakeen\Search\Filters\Title\Contains::NODIACRITIC,
                        ],
                        "labels" => [
                            0 => ___("Contains", "SearchCriteriaUtils.Title simple"),
                            \Anakeen\Search\Filters\Title\Contains::NOT => ___("Does not contain", "SearchCriteriaUtils.Title simple"),
                            \Anakeen\Search\Filters\Title\Contains::STARTSWITH => ___("Starts with", "SearchCriteriaUtils.Title simple"),
                            \Anakeen\Search\Filters\Title\Contains::STARTSWITH + \Anakeen\Search\Filters\Title\Contains::NOT =>
                            ___("Does not start with", "SearchCriteriaUtils.Title simple"),
                        ],
                    ],
                ],
                self::MULTIPLE_FILTER => []
            ],
        ],
        ];
    }

    /**
     * @param $rawData array the json representing the value of the Search Criteria
     * @param bool $deep if true, will check deeply the object
     * @return bool true if the object is correctly formed
     * @throws Exception
     */
    public static function checkRawData($rawData, $deep = true)
    {
        if (self::isMultidimensionalArray($rawData) && $deep) { //If not top level nor leaf level
            foreach ($rawData as $filterValue) {
                self::checkRawData($filterValue);
            }
        } else { //If top level or leaf level
            $kind = $rawData["kind"];
            if (isset($kind) && in_array($kind, self::KIND_SUPPORTED_VALUES)) {
                $logic = $rawData["logic"];
                if (isset($logic) && in_array($logic, self::LOGIC_SUPPORTED_VALUES)) {
                    if ($kind !== self::KIND_FULLTEXT) {
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
                    return true;
                } else {
                    throw new Exception("FLT0014", $logic);
                }
            } else {
                throw new Exception("FLT0013", $kind);
            }
        }
        return true;
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
        $criteriaType = "NOT SUPPORTED";
        switch ($seType) {
            case "htmltext":
            case "password":
            case "json":
            case "xml":
            case "longtext":
            case "text":
                $criteriaType = self::TEXTUAL_KIND;
                break;
            case "account":
            case "docid":
                $criteriaType = self::RELATION_KIND;
                break;
            case "enum":
                $criteriaType = self::ENUM_KIND;
                break;
            case self::FILE_KIND:
            case "image":
                $criteriaType = self::FILE_KIND;
                break;
            case "timestamp":
            case "time":
            case "date":
                $criteriaType = self::TEMPORAL_KIND;
                break;
            case "int":
            case "double":
            case "money":
            case "integer":
                $criteriaType = self::NUMERICAL_KIND;
                break;
            case "title":
                $criteriaType = self::TITLE_KIND;
                break;
            case "state":
                $criteriaType = self::STATE_KIND;
                break;
            default:
                break;
        }
        return $criteriaType;
    }

    /**
     * Builds the filter class given the search criteria information
     * @param string $fieldType
     * @param bool $isFieldMultiple
     * @param bool $isFilterMultiple
     * @param SearchFilterOperator $operator
     * @param string|null $field
     * @param null $value
     * @return mixed the instance of the filter class
     * @throws Exception
     */
    public static function getFilterObject(string $fieldType, bool $isFieldMultiple, bool $isFilterMultiple, SearchFilterOperator $operator, string $field = null, $value = null)
    {
        $fieldMultiple = $isFieldMultiple ? self::MULTIPLE_FIELD : self::SIMPLE_FIELD;
        $filterMultiple = $isFilterMultiple ? self::MULTIPLE_FILTER : self::SIMPLE_FILTER;
        $criteriaType = self::getCriteriaKind($fieldType);
        $typeMap = self::getFilterMap()[$criteriaType][$fieldMultiple][$filterMultiple];
        if (!array_key_exists($operator->key, $typeMap)) {
            throw new Exception("FLT0018", $operator->key, $criteriaType, $isFieldMultiple, $isFilterMultiple);
        }
        $operatorData = $typeMap[$operator->key];
        $class = $operatorData["class"];
        $operands = $operatorData["operands"];
        $appliedOptions = $class::getOptionValue($operator->options);
        $operandsValue = array_map(function ($item) use ($appliedOptions, $field, $value) {
            if ($item && $item === self::FIELD) {
                return $field;
            } elseif ($item === self::VALUE) {
                return $value;
            } else {
                return $item + $appliedOptions;
            }
        }, $operands);
        return new $class(...$operandsValue);
    }

    /**
     * Returns the list of operators for a given smartElement type and multiplicity
     * @param $seType
     * @param bool $fieldMultiple
     * @param string $attrName
     * @return array containing the default operators
     */
    public static function getDefaultOperators($seType, bool $fieldMultiple, string $attrName = "")
    {
        $kind = self::getCriteriaKind($seType);
        $multipleField = $fieldMultiple ? self::MULTIPLE_FIELD : self::SIMPLE_FIELD;
        $map = self::getFilterMap()[$kind][$multipleField];
        return array_merge(
            self::buildDefaultOperatorMap($map, false, $attrName),
            self::buildDefaultOperatorMap($map, true, $attrName)
        );
    }

    /**
     * Returns the list of operators for a given smartElement type and multiplicity
     * @param $seType
     * @param bool $fieldMultiple
     * @return array containing the default operators
     */
    public static function getDefaultOperator($seType, bool $fieldMultiple)
    {
        $kind = self::getCriteriaKind($seType);
        $multipleField = $fieldMultiple ? self::MULTIPLE_FIELD : self::SIMPLE_FIELD;
        $map = self::getFilterMap()[$kind][$multipleField];
        return $map["defaultOperator"];
    }

    /**
     * @param $map
     * @param bool $isMultiple
     * @param string $attrName
     * @return array
     */
    private static function buildDefaultOperatorMap($map, bool $isMultiple, string $attrName = "")
    {
        $operators = array();
        $multipleFilter = $isMultiple ? self::MULTIPLE_FILTER : self::SIMPLE_FILTER;
        foreach ($map[$multipleFilter] as $operatorName => $operatorValue) {
            $operands = $operatorValue["operands"];

            $acceptValues = false; //in_array does not seem to work
            foreach ($operands as $operand) {
                if ($operand === self::VALUE) {
                    $acceptValues = true;
                    break;
                }
            }
            $isBetween = in_array($operatorName, self::BETWEEN_OPERATORS);
            $class = $operatorValue["class"];
            $hasDynamicLabel = array_key_exists("dynamicLabel", $operatorValue) && $operatorValue["dynamicLabel"];
            foreach ($operatorValue["labels"] as $options => $label) {
                $optionAlias = array();
                $decomposedOptions = self::decomposeOptions($options);
                if ($options !== 0) {
                    $optionAlias = $class::getOptionAlias($decomposedOptions);
                }

                $processedLabel = $label;
                if ($hasDynamicLabel) {
                    $processedLabel = sprintf($label, $attrName);
                }

                $op = [
                    "key" => $operatorName,
                    "options" => $optionAlias,
                    "label" => $processedLabel,
                    "acceptValues" => $acceptValues,
                    "filterMultiple" => $isMultiple,
                    "isBetween" => $isBetween,
                ];
                array_push($operators, $op);
            }
        }
        return $operators;
    }

    /**
     * Decompose bitmask options
     * @param $options
     * @return array of int options
     */
    private static function decomposeOptions($options)
    {
        $decomposedOptions = array();
        for ($i = 1; $i <= $options; $i = $i * 2) {
            if (($options & $i) > 0) {
                array_push($decomposedOptions, $i);
            }
        }
        return $decomposedOptions;
    }
}
