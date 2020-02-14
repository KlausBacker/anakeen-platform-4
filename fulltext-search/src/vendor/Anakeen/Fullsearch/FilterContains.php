<?php

namespace Anakeen\Fullsearch;

use Anakeen\Search\Filters\ElementSearchFilter;

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

    public function __construct($domainName, $searchPattern)
    {
        $this->domainName = $domainName;
        $this->searchPattern = $searchPattern;

        $this->domain = new SearchDomain($domainName);
        $this->dbDomain = new SearchDomainDatabase($this->domainName);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        //("id = dochisto(id)");
        $search->join(sprintf("id = %s(id)", $this->dbDomain->getTableName()));

        $search->addFilter(sprintf(
            "to_tsquery('%s', '%s') @@ %s.v",
            "simple",
            pg_escape_string(SearchDomainDatabase::patternToTsquery($this->domain->stem, $this->searchPattern)),
            $this->dbDomain->getTableName()
        ));
    }

    public function getRankOrder()
    {
        return sprintf(
            "ts_rank_cd( %s.v, plainto_tsquery('%s', unaccent('%s'))) desc, %s.id",
            $this->dbDomain->getTableName(),
            pg_escape_string($this->domain->stem),
            pg_escape_string($this->searchPattern),
            $this->dbDomain->getTableName()
        );
    }
}
