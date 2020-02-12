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

    public function __construct($domainName, $searchPattern)
    {
        $this->domainName=$domainName;
        $this->searchPattern = $searchPattern;

        $this->domain = new SearchDomain($domainName);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $dbDomain = new SearchDomainDatabase($this->domainName);
        //("id = dochisto(id)");
        $search->join(sprintf("id = %s(id)", $dbDomain->getTableName()));
        $search->addFilter(sprintf(
            "plainto_tsquery('%s', unaccent('%s')) @@ %s.v",
            pg_escape_string($this->domain->stem),
            pg_escape_string($this->searchPattern),
            $dbDomain->getTableName()
        ));
    }
}
