<?php

namespace Anakeen\Fullsearch;

use Anakeen\Core\DbManager;

class FilterHighlight
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
    protected $startSel="[";
    protected $stopSel="]";

    public function __construct($domainName)
    {
        $this->domainName = $domainName;

        $this->domain = new SearchDomain($domainName);
        $this->dbDomain = new SearchDomainDatabase($this->domainName);
    }


    public function highlight($seId, $pattern)
    {
        $sqlPattern=<<<SQL
select ts_headline('%s', unaccent(ta || ' ' || tb || ' ' || tc || ' ' || td || coalesce((select string_agg(textcontent, ',') from %s where docid=%d), '')), 
                   to_tsquery('simple', '%s'), 'MaxFragments=1,StartSel=%s, StopSel=%s'
) from %s where docid=%d group by ta, tb, tc, td;
;
SQL;


        $sql=sprintf(
            $sqlPattern,
            $this->domain->stem,
            FileContentDatabase::DBTABLE,
            $seId,
            pg_escape_string($this->getPattern($pattern)),
            $this->startSel,
            $this->stopSel,
            $this->dbDomain->getTableName(),
            $seId
        );
        DbManager::query($sql, $r, true, true);

        return $r;
    }


    protected function getPattern($pattern)
    {
        return  SearchDomainDatabase::patternToTsquery($this->domain->stem, $pattern);
    }



    /**
     * @param string $startSel
     * @return FilterHighlight
     */
    public function setStartSel(string $startSel): FilterHighlight
    {
        $this->startSel = $startSel;
        return $this;
    }

    /**
     * @param string $stopSel
     * @return FilterHighlight
     */
    public function setStopSel(string $stopSel): FilterHighlight
    {
        $this->stopSel = $stopSel;
        return $this;
    }
}
