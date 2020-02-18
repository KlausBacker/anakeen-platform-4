<?php

namespace Anakeen\Fullsearch;

use Anakeen\Search\Filters\ElementSearchFilter;
use Anakeen\Search\SearchElements;

class FilterContains implements ElementSearchFilter
{
    protected $domainName;
    protected $searchPattern;
    /**
     * @var SearchDomain
     */
    protected $domain;
    /**
     * @var SearchDomainDatabase
     */
    protected $dbDomain;
    protected $pattern;

    public function __construct($domainName, $searchPattern)
    {
        $this->domainName = $domainName;
        $this->searchPattern = $searchPattern;

        $this->domain = new SearchDomain($domainName);
        $this->dbDomain = new SearchDomainDatabase($this->domainName);
    }


    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        //("id = dochisto(id)");
        $search->join(sprintf("id = %s(id)", $this->dbDomain->getTableName()));

        $search->addFilter(sprintf(
            "to_tsquery('%s', '%s') @@ %s.v",
            "simple",
            pg_escape_string($this->getPattern()),
            $this->dbDomain->getTableName()
        ));
    }

    /**
     * Return sql order to be set in setOrder
     * @see SearchElements::setOrder()
     * @return string
     * @throws \Anakeen\Exception
     */
    public function getRankOrder()
    {
        return sprintf(
            "ts_rank_cd( %s.v, to_tsquery('%s', '%s')) desc, %s.id",
            $this->dbDomain->getTableName(),
            "simple",
            pg_escape_string($this->getPattern()),
            $this->dbDomain->getTableName()
        );
    }

    protected function getPattern()
    {
        if (!$this->pattern) {
            $this->pattern = SearchDomainDatabase::patternToTsquery($this->domain->stem, $this->searchPattern);
        }
        return $this->pattern;
    }
}
