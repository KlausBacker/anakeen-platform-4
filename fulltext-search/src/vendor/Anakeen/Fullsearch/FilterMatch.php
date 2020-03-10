<?php

namespace Anakeen\Fullsearch;

use Anakeen\Search\Filters\ElementSearchFilter;
use Anakeen\Search\SearchElements;

class FilterMatch implements ElementSearchFilter
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

    /**
     * FilterMatch constructor.
     *
     * @param string $domainName Search Domain Identifier
     * @param string $searchPattern terms to search
     */
    public function __construct(string $domainName, string $searchPattern)
    {
        $this->domainName = $domainName;
        $this->searchPattern = $searchPattern;

        $this->domain = new SearchDomain($domainName);
        $this->dbDomain = new SearchDomainDatabase($this->domainName);
    }


    /**
     * Internal method to apply filter
     * @param \Anakeen\Search\Internal\SearchSmartData $search
     * @return string|void
     * @throws \Anakeen\Exception
     * @throws \Anakeen\Search\Exception
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        //("id = dochisto(id)");
        $search->join(sprintf("id = %s(docid)", $this->dbDomain->getTableName()));

        $search->addFilter(sprintf(
            "to_tsquery('%s', '%s') @@ %s.v",
            "simple",
            pg_escape_string($this->getPattern()),
            $this->dbDomain->getTableName()
        ));
    }

    /**
     * Return sql order to be set in setOrder
     * @return string
     * @throws \Anakeen\Exception
     * @see SearchElements::setOrder()
     */
    public function getRankOrder()
    {
        return sprintf(
            "ts_rank_cd( %s.v, to_tsquery('%s', '%s')) desc, %s.docid",
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
