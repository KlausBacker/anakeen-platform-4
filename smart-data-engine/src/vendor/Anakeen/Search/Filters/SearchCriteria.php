<?php


namespace Anakeen\Search\Filters;

use Anakeen\Core\SEManager;
use Anakeen\Search\Internal\SearchSmartData;
use Anakeen\Search\SearchCriteria\SearchCriteriaUtils;
use Anakeen\Search\SearchCriteria\SearchFilter;
use Anakeen\Search\SearchCriteria\SearchFilterAggregator;
use Anakeen\Search\SearchCriteria\SearchFilterOperator;

class SearchCriteria implements ElementSearchFilter
{
    /**
     * @var ElementSearchFilter[]
     */
    protected $filters;
    /**
     * @var SearchFilterAggregator
     */
    protected $searchFilters;


    /**
     * SearchCriteria constructor.
     * @param $searchFilterRawData
     * @throws Exception
     */
    public function __construct($searchFilterRawData)
    {
        $this->searchFilters = new SearchFilterAggregator($searchFilterRawData);
        $this->filters = [];
    }

    /**
     * @inheritDoc
     */
    public function addFilter(SearchSmartData $search)
    {
        $originFilters=$search->getFilters();
        $previousJoin=null;
        $this->filters = $this->parseFilters($this->searchFilters->filters, $search);
        foreach ($this->filters as $filter) {
            $search->join("");
            $search->addFilter($filter);
            $currentJoint=$search->getJoin();
            if ($previousJoin !== null && $previousJoin !== $currentJoint) {
                if (!$previousJoin || !$currentJoint) {
                    throw new Exception("FLT0011", $previousJoin, $currentJoint);
                } else {
                    throw new Exception("FLT0010", $previousJoin, $currentJoint);
                }
            }
            $previousJoin =  $search->getJoin();
        }

        $newFilters=$search->getFilters();


        $andFilters=array_diff($newFilters, $originFilters);
        $andSqlClause = "";
        if (!empty($andFilters)) {
            $andSqlClause = sprintf("(%s)", implode(") and (", $andFilters));
        }
        $search->setFilters($originFilters);
        $search->addFilter($andSqlClause);
    }

    /**
     * Parse criteria raw data to build filters
     * @param array<SearchFilter> $filters : the search criteria raw data
     * @param SearchSmartData $search
     * @return array
     * @throws Exception
     */
    private function parseFilters($filters, SearchSmartData $search)
    {
        $currentFilters = [];

        foreach ($filters as $filter) {
            $operator = $filter->operator;
            if (isset($operator) && $operator->key !== 'na') {
                $fromid = $search->fromid;
                $structureRef = SEManager::getFamily($fromid);
                if (empty($structureRef)) {
                    throw new Exception("FLT0001");
                }
                $kind = $filter->kind;
                if ($kind !== SearchCriteriaUtils::KIND_FULLTEXT) {
                    $value = $filter->value;
                    $isFilterMultiple = $operator->filterMultiple;
                    $field = $filter->field;
                    $isFieldMultiple = false;
                    if ($kind === SearchCriteriaUtils::KIND_VIRTUAL || $kind === SearchCriteriaUtils::KIND_FIELD) {
                        $structField = $structureRef->getAttribute($field);
                        $fieldType = $structField->type;
                        $isFieldMultiple = $structField->isMultiple();
                    } else {
                        $fieldType = SearchCriteriaUtils::getCriteriaKind($field);
                    }
                    $filterObject = SearchCriteriaUtils::getFilterObject(
                        $fieldType,
                        $isFieldMultiple,
                        $isFilterMultiple,
                        $operator,
                        $field,
                        $value
                    );

                    array_push($currentFilters, $filterObject);
                }
            }
        }

        return $currentFilters;
    }
}
