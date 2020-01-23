<?php


namespace Anakeen\Search\Filters;

/**
 * Class OrOperator
 * This operator can be use to use an OR condition between other ElementSearchFilter
 * @package Anakeen\Search\Filters
 */
class OrOperator implements ElementSearchFilter
{

    /**
     * @var ElementSearchFilter[]
     */
    protected $filters;

    public function __construct(ElementSearchFilter ...$filters)
    {
        $this->filters=$filters;
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $originFilters=$search->getFilters();
        $originalJoin=$search->getJoin();
        foreach ($this->filters as $filter) {
            $search->addFilter($filter);

            if ($originalJoin && $search->getJoin() !==$originalJoin) {
                throw new Exception("FLT0010", $originalJoin, $search->getJoin());
            }
            $originalJoin =  $search->getJoin();
        }

        $newFilters=$search->getFilters();


        $orFilters=array_diff($newFilters, $originFilters);

        $orSqlClause=sprintf("(%s)", implode(") or (", $orFilters));
        $search->setFilters($originFilters);
        $search->addFilter($orSqlClause);
    }
}
