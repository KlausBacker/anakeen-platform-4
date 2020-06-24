<?php


namespace Anakeen\SmartCriteria;

use Anakeen\Search\Filters\NAFilter;

class SmartCriteriaInitialConfiguration
{
    public static function getInitialConfiguration()
    {
        return [
            SmartCriteriaConfig::TEXTUAL_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "contains",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Textual simple"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Textual\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Textual simple"),
                                \Anakeen\Search\Filters\Textual\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Textual simple"),
                            ],
                        ],
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\Textual\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.Textual simple"),
                                \Anakeen\Search\Filters\Textual\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.Textual simple"),
                            ],
                        ],
                        "contains" => [
                            "class" => \Anakeen\Search\Filters\Textual\Contains::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE,
                                \Anakeen\Search\Filters\Textual\Contains::NOCASE + \Anakeen\Search\Filters\Textual\Contains::NODIACRITIC],
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
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ],
                SmartCriteriaConfig::MULTIPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "oneContains",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Textual multi"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Textual\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtilsTextual multi"),
                                \Anakeen\Search\Filters\Textual\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtilsTextual multi"),
                            ],
                        ],
                        "oneEmpty" => [
                            "class" => \Anakeen\Search\Filters\Textual\OneEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One value is empty", "SearchCriteriaUtils.Textual multi"),
                                \Anakeen\Search\Filters\Textual\OneEmpty::NOT => ___("One value is not empty", "SearchCriteriaUtils.Textual multi"),
                                \Anakeen\Search\Filters\Textual\OneEmpty::ALL => ___("All values are empty", "SearchCriteriaUtils.Textual multi"),
                                \Anakeen\Search\Filters\Textual\OneEmpty::ALL + \Anakeen\Search\Filters\Textual\OneEmpty::NOT =>
                                    ___("No value is empty", "SearchCriteriaUtils.Textual multi"),
                            ],
                        ],
                        "oneEquals" => [
                            "class" => \Anakeen\Search\Filters\Textual\OneEquals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One value is equal to", "SearchCriteriaUtils.Textual multi"),
                                \Anakeen\Search\Filters\Textual\OneEquals::NOT => ___("One value is different from", "SearchCriteriaUtils.Textual multi"),
                                \Anakeen\Search\Filters\Textual\OneEquals::ALL => ___("All values are equal to", "SearchCriteriaUtils.Textual multi"),
                                \Anakeen\Search\Filters\Textual\OneEquals::ALL + \Anakeen\Search\Filters\Textual\OneEquals::NOT =>
                                    ___("All values are different from", "SearchCriteriaUtils.Textual multi"),
                            ],
                        ],
                        "oneContains" => [
                            "class" => \Anakeen\Search\Filters\Textual\OneContains::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE,
                                \Anakeen\Search\Filters\Textual\OneContains::NOCASE + \Anakeen\Search\Filters\Textual\OneContains::NODIACRITIC],
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
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ]
            ],
            SmartCriteriaConfig::TEMPORAL_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "between",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Temporal simple"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Temporal\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Temporal simple"),
                                \Anakeen\Search\Filters\Temporal\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Temporal simple"),
                            ],
                        ],
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\Temporal\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.Temporal simple"),
                                \Anakeen\Search\Filters\Temporal\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.Temporal simple"),
                            ],
                        ],
                        "lesser" => [
                            "class" => \Anakeen\Search\Filters\Temporal\Lesser::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is strictly before", "SearchCriteriaUtils.Temporal simple"),
                                \Anakeen\Search\Filters\Temporal\Lesser::EQUAL => ___("Is before", "SearchCriteriaUtils.Temporal simple"),
                            ],
                        ],
                        "greater" => [
                            "class" => \Anakeen\Search\Filters\Temporal\Greater::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is strictly after", "SearchCriteriaUtils.Temporal simple"),
                                \Anakeen\Search\Filters\Temporal\Greater::EQUAL => ___("Is after", "SearchCriteriaUtils.Temporal simple"),
                            ],
                        ],
                        "between" => [
                            "class" => \Anakeen\Search\Filters\Temporal\Between::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE,
                                \Anakeen\Search\Filters\Temporal\Between::EQUALRIGHT + \Anakeen\Search\Filters\Temporal\Between::EQUALLEFT],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is between", "SearchCriteriaUtils.Temporal simple"),
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ],
                SmartCriteriaConfig::MULTIPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "oneBetween",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Temporal multi"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Temporal\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Temporal multi"),
                            ],
                        ],
                        "oneEmpty" => [
                            "class" => \Anakeen\Search\Filters\Temporal\OneEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One date is empty", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneEmpty::NOT => ___("One date is not empty", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneEmpty::ALL => ___("All dates are empty", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneEmpty::ALL + \Anakeen\Search\Filters\Temporal\OneEmpty::NOT =>
                                    ___("No date is empty", "SearchCriteriaUtils.Temporal multi"),
                            ],
                        ],
                        "oneEquals" => [
                            "class" => \Anakeen\Search\Filters\Temporal\OneEquals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One date is equal to", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneEquals::NOT => ___("One date is different from", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneEquals::ALL => ___("All dates are equal to", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneEquals::NOT + \Anakeen\Search\Filters\Temporal\OneEquals::ALL =>
                                    ___("All dates are different from", "SearchCriteriaUtils.Temporal multi"),
                            ],
                        ],
                        "oneLesser" => [
                            "class" => \Anakeen\Search\Filters\Temporal\OneLesser::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One date is strictly before", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneLesser::EQUAL => ___("One date is before", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneLesser::ALL => ___("All dates are strictly before", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneLesser::ALL + \Anakeen\Search\Filters\Temporal\OneLesser::EQUAL =>
                                    ___("All dates are before", "SearchCriteriaUtils.Temporal multi"),
                            ],
                        ],
                        "oneGreater" => [
                            "class" => \Anakeen\Search\Filters\Temporal\OneGreater::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One date is strictly after", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneGreater::EQUAL => ___("One date is after", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneGreater::ALL => ___("All dates are strictly after", "SearchCriteriaUtils.Temporal multi"),
                                \Anakeen\Search\Filters\Temporal\OneGreater::ALL + \Anakeen\Search\Filters\Temporal\OneGreater::EQUAL =>
                                    ___("All dates are after", "SearchCriteriaUtils.Temporal multi"),
                            ],
                        ],
                        "oneBetween" => [
                            "class" => \Anakeen\Search\Filters\Temporal\OneBetween::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
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
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ]
            ],
            SmartCriteriaConfig::NUMERICAL_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "between",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Numerical simple"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Numerical\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Numerical simple"),
                                \Anakeen\Search\Filters\Numerical\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Numerical simple"),
                            ],
                        ],
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\Numerical\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.Numerical simple"),
                                \Anakeen\Search\Filters\Numerical\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.Numerical simple"),
                            ],
                        ],
                        "lesser" => [
                            "class" => \Anakeen\Search\Filters\Numerical\Lesser::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is strictly lesser than", "SearchCriteriaUtils.Numerical simple"),
                                \Anakeen\Search\Filters\Numerical\Lesser::EQUAL => ___("Is lesser than", "SearchCriteriaUtils.Numerical simple"),
                            ],
                        ],
                        "greater" => [
                            "class" => \Anakeen\Search\Filters\Numerical\Greater::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is strictly greater than", "SearchCriteriaUtils.Numerical simple"),
                                \Anakeen\Search\Filters\Numerical\Greater::EQUAL => ___("Is greater than", "SearchCriteriaUtils.Numerical simple"),
                            ],
                        ],
                        "between" => [
                            "class" => \Anakeen\Search\Filters\Numerical\Between::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE,
                                \Anakeen\Search\Filters\Numerical\Between::EQUALRIGHT + \Anakeen\Search\Filters\Numerical\Between::EQUALLEFT],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is between", "SearchCriteriaUtils.Numerical simple"),
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ],
                SmartCriteriaConfig::MULTIPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "oneBetween",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Numerical multi"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Numerical\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Numerical multi"),
                            ],
                        ],
                        "oneEmpty" => [
                            "class" => \Anakeen\Search\Filters\Numerical\OneEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One date is empty", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneEmpty::NOT => ___("One date is not empty", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneEmpty::ALL => ___("All dates are empty", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneEmpty::ALL + \Anakeen\Search\Filters\Numerical\OneEmpty::NOT =>
                                    ___("No date is empty", "SearchCriteriaUtils.Numerical multi"),
                            ],
                        ],
                        "oneEquals" => [
                            "class" => \Anakeen\Search\Filters\Numerical\OneEquals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One value is equal to", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneEquals::NOT => ___("One value is different from", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneEquals::ALL => ___("All values are equal to", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneEquals::ALL + \Anakeen\Search\Filters\Numerical\OneEquals::NOT =>
                                    ___("All values are different from", "SearchCriteriaUtils.Numerical multi"),
                            ],
                        ],
                        "oneLesser" => [
                            "class" => \Anakeen\Search\Filters\Numerical\OneLesser::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One value is strictly lesser than", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneLesser::EQUAL => ___("One value is lesser than", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneLesser::ALL => ___("All values are strictly lesser than", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneLesser::ALL + \Anakeen\Search\Filters\Numerical\OneLesser::EQUAL =>
                                    ___("All values are lesser than", "SearchCriteriaUtils.Numerical multi"),
                            ],
                        ],
                        "oneGreater" => [
                            "class" => \Anakeen\Search\Filters\Numerical\OneGreater::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("One value is strictly greater than", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneGreater::EQUAL => ___("One value is greater than", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneGreater::ALL => ___("All values are strictly greater than", "SearchCriteriaUtils.Numerical multi"),
                                \Anakeen\Search\Filters\Numerical\OneGreater::ALL + \Anakeen\Search\Filters\Numerical\OneGreater::EQUAL =>
                                    ___("All values are greater than", "SearchCriteriaUtils.Numerical multi"),
                            ],
                        ],
                        "oneBetween" => [
                            "class" => \Anakeen\Search\Filters\Numerical\OneBetween::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE,
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
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ]
            ],
            SmartCriteriaConfig::RELATION_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "equalsOne",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Relations simple"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Relation\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Relations simple"),
                                \Anakeen\Search\Filters\Relation\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Relations simple"),
                            ],
                        ],
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\Relation\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.Relations simple"),
                                \Anakeen\Search\Filters\Relation\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.Relations simple"),
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => [
                        "equalsOne" => [
                            "class" => \Anakeen\Search\Filters\Relation\EqualsOne::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is among", "SearchCriteriaUtils.Relations simple"),
                                \Anakeen\Search\Filters\Relation\EqualsOne::NOT => ___("Is not among", "SearchCriteriaUtils.Relations simple"),
                            ],
                        ],
                    ]
                ],
                SmartCriteriaConfig::MULTIPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "oneEqualsMulti",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Relations multi"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Relation\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Relations multi"),
                                \Anakeen\Search\Filters\Relation\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Relations multi"),
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => [
                        "oneEqualsMulti" => [
                            "class" => \Anakeen\Search\Filters\Relation\OneEqualsMulti::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
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
            SmartCriteriaConfig::ENUM_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "equalsOne",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Enum simple"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Enum\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Enum simple"),
                                \Anakeen\Search\Filters\Enum\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Enum simple"),
                            ],
                        ],
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\Enum\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.Enum simple"),
                                \Anakeen\Search\Filters\Enum\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.Enum simple"),
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => [
                        "equalsOne" => [
                            "class" => \Anakeen\Search\Filters\Enum\EqualsOne::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is among", "SearchCriteriaUtils.Enum simple"),
                                \Anakeen\Search\Filters\Enum\EqualsOne::NOT => ___("Is not among", "SearchCriteriaUtils.Enum simple"),
                            ],
                        ],
                    ]
                ],
                SmartCriteriaConfig::MULTIPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "oneEqualsMulti",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.Enum multi"),
                            ],
                        ],
                        "isEmpty" => [
                            "class" => \Anakeen\Search\Filters\Enum\IsEmpty::class,
                            "operands" => [SmartCriteriaConfig::FIELD, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is empty", "SearchCriteriaUtils.Enum multi"),
                                \Anakeen\Search\Filters\Enum\IsEmpty::NOT => ___("Is not empty", "SearchCriteriaUtils.Enum multi"),
                            ],
                        ],
                        "oneEquals" => [
                            "class" => \Anakeen\Search\Filters\Enum\OneEquals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [0 => ___("One value is equal to", "SearchCriteriaUtils.Enum multi"),
                                \Anakeen\Search\Filters\Enum\OneEquals::NOT => ___(
                                    "One value is different from",
                                    "SearchCriteriaUtils.Enum multi"
                                ),
                                \Anakeen\Search\Filters\Enum\OneEquals::ALL => ___(
                                    "All values are equal to",
                                    "SearchCriteriaUtils.Enum multi"
                                ),
                                \Anakeen\Search\Filters\Enum\OneEquals::ALL + \Anakeen\Search\Filters\Enum\OneEquals::NOT => ___(
                                    "All values are different from",
                                    "SearchCriteriaUtils.Enum multi"
                                )
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => [
                        "oneEqualsMulti" => [
                            "class" => \Anakeen\Search\Filters\Relation\OneEqualsMulti::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
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
            SmartCriteriaConfig::STATE_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "equalsOne",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("NA", "SearchCriteriaUtils.State simple"),
                            ],
                        ],
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\State\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.State simple"),
                                \Anakeen\Search\Filters\State\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.State simple"),
                            ],
                        ],
                    ],
                    SmartCriteriaConfig::MULTIPLE_FILTER => [
                        "equalsOne" => [
                            "class" => \Anakeen\Search\Filters\State\EqualsOne::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is among", "SearchCriteriaUtils.State simple"),
                                \Anakeen\Search\Filters\State\EqualsOne::NOT => ___("Is not among", "SearchCriteriaUtils.State simple"),
                            ],
                        ],
                    ]
                ],
            ],
            SmartCriteriaConfig::TITLE_KIND => [
                SmartCriteriaConfig::SIMPLE_FIELD => [
                    "defaultOperator" => [
                        "key" => "contains",
                        "options" => [],
                    ],
                    SmartCriteriaConfig::SIMPLE_FILTER => [
                        "none" => [
                            "class" => NAFilter::class,
                            "operands" => [SmartCriteriaConfig::FIELD],
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
                        "equals" => [
                            "class" => \Anakeen\Search\Filters\Title\Equals::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE, 0],
                            "availableOptions" => [],
                            "labels" => [
                                0 => ___("Is equal to", "SearchCriteriaUtils.Title simple"),
                                \Anakeen\Search\Filters\Title\Equals::NOT => ___("Is different from", "SearchCriteriaUtils.Title simple"),
                            ],
                        ],
                        "contains" => [
                            "class" => \Anakeen\Search\Filters\Title\Contains::class,
                            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE,
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
                    SmartCriteriaConfig::MULTIPLE_FILTER => []
                ],
            ],
        ];
    }
}
