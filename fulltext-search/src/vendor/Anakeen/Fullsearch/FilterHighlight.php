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
    protected $startSel = "{{";
    protected $stopSel = "}}";


    /**
     * FilterHighlight constructor.
     *
     * @param string $domainName Search Domain Identifier
     */
    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;

        $this->domain = new SearchDomain($domainName);
        $this->dbDomain = new SearchDomainDatabase($this->domainName);
    }


    /**
     * Return part of text where pattern is found
     * @param string $seId Smart Element Identifier
     * @param string $pattern terme to highlight
     *
     * @return mixed
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    public function highlight($seId, $pattern)
    {
        $sql = sprintf(
            "select ts_headline('%s', (ta || ' ' || tb || ' ' || tc || ' ' || td || coalesce((select string_agg(textcontent, ',') 
                    from %s where fileid in (select unnest(files) from %s where docid=%d)), '')), 
                   to_tsquery('simple', '%s'), 'MaxFragments=1,StartSel=%s, StopSel=%s') from %s where docid=%d group by ta, tb, tc, td;",
            $this->domain->stem,
            FileContentDatabase::DBTABLE,
            $this->dbDomain->getTableName(),
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
        return SearchDomainDatabase::patternToTsquery($this->domain->stem, $pattern);
    }

    /**
     * Set characters to identify the begining of match term
     * @param string $startSel default is "{{"
     * @return FilterHighlight
     */
    public function setStartSel(string $startSel): FilterHighlight
    {
        $this->startSel = $startSel;
        return $this;
    }

    /**
     * Set characters to identify the ending of match term
     * @param string $stopSel  default is "}}"
     * @return FilterHighlight
     */
    public function setStopSel(string $stopSel): FilterHighlight
    {
        $this->stopSel = $stopSel;
        return $this;
    }
}
