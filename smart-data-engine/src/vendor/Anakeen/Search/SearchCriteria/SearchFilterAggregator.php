<?php


namespace Anakeen\Search\SearchCriteria;

use Anakeen\Search\Filters\Exception;

class SearchFilterAggregator
{
    /**
     * @var array<SearchFilter>
     */
    public $filters = array();

    /**
     * SearchFilter constructor.
     * @param $rawData array the json representing the value of the Search Criteria
     * @throws Exception
     */
    public function __construct($rawData)
    {
        if ($rawData["disabled"] !== true) {
            SearchCriteriaUtils::checkRawData($rawData);
            $this->parseData($rawData);
        }
    }

    /**
     * @param $rawData array the rawData of the search criteria
     */
    public function parseData($rawData)
    {
        if (SearchCriteriaUtils::isMultidimensionalArray($rawData)) { //If not top level nor leaf level
            foreach ($rawData as $filterValue) {
                $this->parseData($filterValue);
            }
        } else { //If top level or leaf level
            $kind = $rawData["kind"];
            $logic = $rawData["logic"];
            $field = $rawData["field"];
            $value = $rawData["value"];

            $operatorArray = $rawData["operator"];
            $options = $operatorArray["options"];
            $additionalOptions = array();
            if (array_key_exists("additionalOptions", $operatorArray)) {
                $additionalOptions = $operatorArray["additionalOptions"];
            }
            $operator = new SearchFilterOperator($operatorArray["key"], $options, $operatorArray["filterMultiple"], $additionalOptions);
            array_push($this->filters, new SearchFilter($kind, $field, $operator, $value, $logic));

            if (array_key_exists("filters", $rawData)) {
                $filters = $rawData["filters"];
                if (is_array($filters) && count($filters) > 0) {
                    $this->parseData($filters);
                }
            }
        }
    }
}
