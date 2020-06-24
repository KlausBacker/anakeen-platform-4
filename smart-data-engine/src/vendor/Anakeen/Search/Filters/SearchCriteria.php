<?php


namespace Anakeen\Search\Filters;

use Anakeen\Core\SEManager;
use Anakeen\Search\Internal\SearchSmartData;
use Anakeen\SmartCriteria\SmartCriteriaConfig;
use Anakeen\SmartCriteria\SmartCriteriaConfigurationSingleton;
use Anakeen\SmartCriteria\SmartCriteriaUtils;
use Anakeen\SmartCriteria\SmartFilter;
use Anakeen\SmartCriteria\SmartFilterAggregator;

class SearchCriteria implements ElementSearchFilter
{
    /**
     * @var ElementSearchFilter[]
     */
    protected $filters;
    /**
     * @var SmartFilterAggregator
     */
    protected $searchFilters;


    /**
     * SearchCriteria constructor.
     * @param $searchFilterRawData
     * @throws Exception
     */
    public function __construct($searchFilterRawData)
    {
        $this->searchFilters = new SmartFilterAggregator($searchFilterRawData);
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
     * @param array<SmartFilter> $filters : the smart criteria raw data
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
                $value = $filter->value;
                $isFilterMultiple = isset($operator->filterMultiple) ? $operator->filterMultiple : false;
                $field = $filter->field;
                $isFieldMultiple = false;
                if ($kind === SmartCriteriaConfig::KIND_VIRTUAL || $kind === SmartCriteriaConfig::KIND_FIELD) {
                    $structField = $structureRef->getAttribute($field);
                    $fieldType = $structField->type;
                    $isFieldMultiple = $structField->isMultiple();
                } elseif ($kind === SmartCriteriaConfig::KIND_PROPERTY) {
                    $fieldType = SmartCriteriaUtils::getCriteriaKind($field);
                } else {
                    $fieldType = $kind;
                }
                $filterObject = SmartCriteriaConfigurationSingleton::getInstance()->getFilterObject(
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

        return $currentFilters;
    }
}
