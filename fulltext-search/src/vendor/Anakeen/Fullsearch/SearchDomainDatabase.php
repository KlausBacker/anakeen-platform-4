<?php

namespace Anakeen\Fullsearch;

require_once __DIR__ . "/lib/vendor/autoload.php";

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Core\Utils\Postgres;
use Anakeen\Core\Utils\Strings;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;
use Tmilos\Lexer\Config\LexerArrayConfig;
use Tmilos\Lexer\Config\TokenDefn;
use Tmilos\Lexer\Lexer;

class SearchDomainDatabase
{
    const dbSchema = "searches";
    /**
     * @var string
     */
    protected $domainName;
    /**
     * @var SearchDomain
     */
    protected $domain;
    /**
     * @var \Closure
     */
    protected $updateHook;


    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
        $this->domain = new SearchDomain($this->domainName);
    }

    /**
     * Initialize : create table and index
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public function initialize()
    {
        $sql = sprintf("create schema if not exists %s ", pg_escape_identifier(self::dbSchema));

        DbManager::query($sql);

        $this->createTable();
        $this->createIndex();
    }

    public function clearData()
    {
        $sql = sprintf("delete from %s", $this->getTableName());
        DbManager::query($sql);
    }

    /**
     * Reindex all data that are not up-to-date described in search domain
     * @param bool $clearDataBefore if true, previous recorded data are deleted before record data
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Search\Exception
     */
    public function recordData(bool $clearDataBefore)
    {
        $sessionId = crc32($this->domain->name);
        $isLocked = DbManager::lockSession($sessionId, true);
        if (!$isLocked) {
            throw new Exception("FSEA0012", $this->domainName);
        }
        $currentLanguage = ContextManager::getLanguage();
        if ($this->domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($this->domain->lang);
        }

        if ($clearDataBefore === true) {
            $this->clearData();
        }

        $configs = $this->domain->configs;
        foreach ($configs as $smartStructureSearchconfig) {
            $fields = array_map(function ($item) {
                /** @var SearchFieldConfig $item */
                return $item->field;
            }, $smartStructureSearchconfig->fields);

            $fields = array_merge($fields, array_map(function ($item) {
                /** @var SearchFieldConfig $item */
                return $item->field;
            }, $smartStructureSearchconfig->files));

            $fields[] = "mdate";

            $structureName = $smartStructureSearchconfig->structure;
            $structure = SEManager::getFamily($structureName);
            if (!$structure) {
                throw new Exception("FSEA0003", $this->domainName, $structureName);
            }

            $s = new SearchElements($structure->id);
            if ($smartStructureSearchconfig->collection) {
                $s->useCollection($smartStructureSearchconfig->collection);
            }
            $s->join(sprintf("id = %s(docid)", $this->getTableName()), "left outer");
            $s->addFilter(
                "%s.mdate > %s.mdate or %s.mdate is null",
                $s->getMainTable(),
                $this->getTableName(),
                $this->getTableName()
            );
            $s->overrideAccessControl();
            if (!$smartStructureSearchconfig->callables) {
                $s->returnsOnly($fields);
            }
            $s->setOrder("id");

            $results = $s->search()->getResults();

            $ft = $this->updateHook;
            foreach ($results as $se) {
                //printf("%05d/%d %s)\n",$c++,$count, $structureName);
                if ($ft) {
                    $ft($se);
                }
                $this->updateSmartElementIndex($se, $smartStructureSearchconfig);
            }
        }


        if ($clearDataBefore === true) {
            $sql = <<<SQL
update %s set v = 
setweight(to_tsvector('%s', ta), 'A') || 
setweight(to_tsvector('%s', tb), 'B') || 
setweight(to_tsvector('%s', tc), 'C') || 
setweight(to_tsvector('%s', td), 'D');
SQL;
            DbManager::query(sprintf(
                $sql,
                $this->getTableName(),
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem
            ));
        }

        if ($this->domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($currentLanguage);
        }

        DbManager::unlockSession($sessionId);
    }

    public function onUpdate(\Closure $onUpdate)
    {
        $this->updateHook = $onUpdate;
    }

    public function getDbStats()
    {
        $configs = $this->domain->configs;
        $stats = [];
        foreach ($configs as $smartStructureSearchconfig) {
            $structureName = $smartStructureSearchconfig->structure;
            $structure = SEManager::getFamily($structureName);
            if (!$structure) {
                throw new Exception("FSEA0003", $this->domainName, $structureName);
            }
            $s = new SearchElements($structure->id);
            $s->overrideAccessControl();
            if ($smartStructureSearchconfig->collection) {
                $s->useCollection($smartStructureSearchconfig->collection);
            }
            $results = $s->onlyCount();

            $stats["structures"][$structureName]["totalToIndex"] = $results;


            $s = new SearchElements($structure->id);
            $s->overrideAccessControl();
            if ($smartStructureSearchconfig->collection) {
                $s->useCollection($smartStructureSearchconfig->collection);
            }
            $s->join(sprintf("id = %s(docid)", $this->getTableName()));
            $results = $s->onlyCount();

            $stats["structures"][$structureName]["totalIndexed"] = $results;


            $s = new SearchElements($structure->id);
            $s->overrideAccessControl();
            if ($smartStructureSearchconfig->collection) {
                $s->useCollection($smartStructureSearchconfig->collection);
            }
            $s->join(sprintf("id = %s(docid)", $this->getTableName()));
            $s->addFilter(
                "%s.mdate > %s.mdate",
                $s->getMainTable(),
                $this->getTableName()
            );
            $results = $s->onlyCount();

            $stats["structures"][$structureName]["totalDirty"] = $results;
        }
        $sql = sprintf(
            "select f.status, count(f.status) from %s f inner join %s s on (f.fileid = any(s.files))  group by f.status",
            FileContentDatabase::DBTABLE,
            $this->getTableName()
        );
        DbManager::query($sql, $status);
        $dbStatus = array_map(function ($item) {
            return $item["status"];
        }, $status);

        foreach (["W", "K", "D"] as $codeStatus) {
            if (!in_array($codeStatus, $dbStatus)) {
                $status[] = ["status" => $codeStatus, "count" => 0];
            }
        }

        foreach ($status as &$aStatus) {
            switch ($aStatus["status"]) {
                case "K":
                    $aStatus["label"] = ___("Failing", "fullsearch-status");
                    break;
                case "D":
                    $aStatus["label"] = ___("Succeed", "fullsearch-status");
                    break;
                case "W":
                    $aStatus["label"] = ___("Waiting", "fullsearch-status");
                    break;
                default:
                    $aStatus["label"] = $aStatus["status"];
                    break;
            }
        }


        $stats["files"] = $status;
        return $stats;
    }

    /**
     * Return all failed files indexing
     * @TODO Add admin interface to see failing files details
     * @return mixed
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public function getFailingFiles()
    {
        $sql = sprintf(
            "select f.fileid, v.name as filename, docread.title, docread.id 
                from %s f inner join %s s on (f.fileid = any(s.files)) 
                inner join docread on (docread.id = s.docid) 
                inner join vaultdiskstorage v on  (v.id_file=f.fileid) 
                where status='K'",
            FileContentDatabase::DBTABLE,
            $this->getTableName()
        );

        DbManager::query($sql, $results);
        return $results;
    }

    /**
     * Reset indexing of all search data for the smart element
     * @param SmartElement $se
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public function updateSmartElement(SmartElement $se)
    {
        $configs = $this->domain->configs;
        $config = "";
        foreach ($configs as $smartStructureSearchconfig) {
            $structureClass = SEManager::getFamilyClassName($smartStructureSearchconfig->structure);

            if (is_a($se, $structureClass)) {
                if ($smartStructureSearchconfig->collection) {
                    $s = new SearchElements($smartStructureSearchconfig->structure);
                    $s->useCollection($smartStructureSearchconfig->collection);
                    $s->addFilter(
                        "initid = %d",
                        $se->initid
                    );
                    $s->search();
                    if ($s->count() > 0) {
                        $config = $smartStructureSearchconfig;
                        break;
                    }
                } else {
                    $config = $smartStructureSearchconfig;
                    break;
                }
            }
        }
        if (!$config) {
            throw new Exception("FSEA0006", $this->domainName, $se->fromname);
        }
        $currentLanguage = ContextManager::getLanguage();
        if ($this->domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($this->domain->lang);
        }
        // Delete before reset searching data
        $sql = sprintf(
            "delete from %s where docid=%d",
            $this->getTableName(),
            $se->id
        );
        DbManager::query($sql);
        $this->updateSmartElementIndex($se, $config);
        if ($this->domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($currentLanguage);
        }
    }

    /**
     * Get database table name (with schema) where search data are recorded
     * @return string
     * @throws Exception
     */
    public function getTableName()
    {
        if (!preg_match('/^([a-z0-9_]{1,64})$/i', $this->domainName)) {
            throw new Exception("FSEA0004", $this->domainName);
        }
        return strtolower(sprintf(
            "%s.%s",
            self::dbSchema,
            $this->domainName
        ));
    }

    /**
     * Return element id that reference the file
     * @param int $fileid file identifier
     * @return int[] element ids
     */
    public function getElementIdsReferenceFile($fileid)
    {
        $sql = sprintf(
            "
                select s.docid
                from %s f
                inner join %s s on (f.fileid = any(s.files))
                where f.fileid=%s;",
            FileContentDatabase::DBTABLE,
            $this->getTableName(),
            intval($fileid)
        );
        DbManager::query($sql, $docids, true);
        return $docids;
    }

    /**
     * Update data index with file data set in files schema
     * Call after TE result record file text conversion is done
     * @param SmartElement $se
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public function updateSmartWithFiles(SmartElement $se)
    {
        $configs = $this->domain->configs;
        foreach ($configs as $config) {
            $structureName = $config->structure;
            if (!is_a($se, SEManager::getFamilyClassName($structureName))) {
                continue;
            }

            $weightFiles = ["A" => [], "B" => [], "C" => [], "D" => []];
            foreach ($config->files as $fieldInfo) {
                $oa = $se->getAttribute($fieldInfo->field);
                if ($oa === false) {
                    continue;
                }
                $rawValue = $se->getRawValue($oa->id);
                if (!$rawValue) {
                    continue;
                }
                if ($oa->type === "file") {
                    if ($oa->isMultiple() === false) {
                        if (preg_match(PREGEXPFILE, $rawValue, $reg)) {
                            if ($reg["vid"]) {
                                $weightFiles[$fieldInfo->weight][] = $reg["vid"];
                            }
                        }
                    } else {
                        $rawValues = $se->getMultipleRawValues($oa->id);
                        foreach ($rawValues as $rawValue) {
                            if (preg_match(PREGEXPFILE, $rawValue, $reg)) {
                                if ($reg["vid"]) {
                                    $weightFiles[$fieldInfo->weight][] = $reg["vid"];
                                }
                            }
                        }
                    }
                }
            }


            $sql = <<<SQL
update searches.%s as s set v = 

setweight(to_tsvector('%s', ta), 'A') || 
setweight(to_tsvector('%s', tb), 'B') || 
setweight(to_tsvector('%s', tc), 'C') || 
setweight(to_tsvector('%s', td), 'D') 

%s

where s.docid = %d
SQL;
            $sqlset = [];
            foreach ($weightFiles as $weight => $fileFields) {
                if ($fileFields) {
                    $sqlset[] = sprintf(
                        "setweight((select to_tsvector('%s', string_agg(textcontent, ', ')) from %s where fileid in (%s) and status='D'), '%s')",
                        $this->domain->stem,
                        FileContentDatabase::DBTABLE,
                        implode(", ", $fileFields),
                        pg_escape_string($weight)
                    );
                }
            }

            if ($sqlset) {
                $filedata = ' || ' . implode(' || ', $sqlset);
            } else {
                $filedata = '';
            }

            $updSql = sprintf(
                $sql,
                $this->domain->name,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                $filedata,
                $se->id
            );
            DbManager::query($updSql);
        }
    }

    /**
     * Convert web pattern to ts query,
     * @param string $stem stemmer : french , english, ...
     * @param string $pattern google like pattern
     * @return string the ts query
     * @throws \Anakeen\Database\Exception
     */
    public static function patternToTsquery($stem, $pattern)
    {
        $config = new LexerArrayConfig([]);

        $config->addTokenDefinition(new TokenDefn("NOT", '(?:^|\\s)-', "u"));
        $config->addTokenDefinition(new TokenDefn("OR", '\\sor\\s', "u"));
        $config->addTokenDefinition(new TokenDefn("PHRASE", '"[^"]+"', "u"));
        $config->addTokenDefinition(new TokenDefn("SPACES", "\\s+", "u"));
        $config->addTokenDefinition(new TokenDefn("STARTWITH", '[\\p{L}\\p{N}]+\\*(?:\\s|$)', "u"));
        $config->addTokenDefinition(new TokenDefn("PLAIN", "[^\\s]+", "u"));


        $pattern = str_replace("'", " ", $pattern);
        $lexer = new Lexer($config);
        $lexer->setInput($pattern);
        $lexer->moveNext();

        $parts = [];
        $currentSequence = "";
        while ($lexer->getLookahead()) {
            $tokenName = $lexer->getLookahead()->getName();
            //print $tokenName.">".$lexer->getLookahead()->getValue()."\n";
            if ($tokenName === "PLAIN" || $tokenName === "SPACES") {
                $currentSequence .= $lexer->getLookahead()->getValue();
            } else {
                if ($currentSequence) {
                    $parts[] = [
                        "token" => "PLAIN",
                        "value" => $currentSequence

                    ];
                    $currentSequence = "";
                }
                $parts[] = [
                    "token" => $tokenName,
                    "value" => $lexer->getLookahead()->getValue()
                ];
            }


            $lexer->moveNext();
        }
        if ($currentSequence) {
            $parts[] = [
                "token" => "PLAIN",
                "value" => $currentSequence

            ];
        }
        $toQuery = [];
        foreach ($parts as $k => $part) {
            switch ($part["token"]) {
                case "PLAIN":
                    $toQuery[] = sprintf(
                        "plainto_tsquery('%s', '%s') as q%d",
                        pg_escape_string($stem),
                        pg_escape_string($part["value"]),
                        $k
                    );
                    break;
                case "PHRASE":
                    $toQuery[] = sprintf(
                        "phraseto_tsquery('%s', '%s') as q%d",
                        pg_escape_string($stem),
                        pg_escape_string(trim($part["value"], '"')),
                        $k
                    );
                    break;
            }
        }

        $sql = sprintf("select %s", implode(", ", $toQuery));
        DbManager::query($sql, $queryResults, false, true);

        $finalQueryParts = [];
        $needAddAndOperator = false;
        foreach ($parts as $k => $part) {
            switch ($part["token"]) {
                case "PHRASE":
                case "PLAIN":
                    if (!empty($queryResults["q$k"])) {
                        if ($needAddAndOperator === true) {
                            $finalQueryParts[] = " & ";
                        }
                        $needAddAndOperator = true;
                        $finalQueryParts[] = sprintf("(%s)", $queryResults["q$k"]);
                    }
                    break;
                case "OR":
                    $finalQueryParts[] = " | ";
                    $needAddAndOperator = false;
                    break;
                case "NOT":
                    if ($needAddAndOperator === true) {
                        $finalQueryParts[] = " & ";
                    }
                    $finalQueryParts[] = "!";
                    $needAddAndOperator = false;
                    break;
                case "STARTWITH":
                    if ($needAddAndOperator === true) {
                        $finalQueryParts[] = " & ";
                    }
                    $startWord = trim($part["value"], " *");
                    $finalQueryParts[] = sprintf("%s:* ", Strings::unaccent($startWord));
                    $needAddAndOperator = true;
            }
        }

        return implode("", $finalQueryParts);
    }

    /**
     * Update data vector index without file data
     * @param SmartElement $se
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    protected function updateTsVector(SmartElement $se)
    {
        $configs = $this->domain->configs;
        foreach ($configs as $config) {
            $structureName = $config->structure;
            if (!is_a($se, SEManager::getFamilyClassName($structureName))) {
                continue;
            }


            $sql = <<<SQL
update searches.%s as s set v = 

setweight(to_tsvector('%s', ta), 'A') || 
setweight(to_tsvector('%s', tb), 'B') || 
setweight(to_tsvector('%s', tc), 'C') || 
setweight(to_tsvector('%s', td), 'D') 

where s.docid = %d
SQL;


            $updSql = sprintf(
                $sql,
                $this->domain->name,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                $se->id
            );
            DbManager::query($updSql);
        }
    }


    protected function createIndex()
    {
        $sql = <<<SQL
CREATE INDEX if not exists vsearch_idx_%s ON %s USING GIN (v);
SQL;
        DbManager::query(sprintf($sql, $this->domainName, $this->getTableName()));
    }

    protected function createTable()
    {
        $sql = <<<SQL
create table if not exists %s  (
  docid int references docread(id),
  ta text default '',
  tb text default '',
  tc text default '',
  td text default '',
  files bigint[] default '{}',
  mdate timestamp,
  v tsvector,
  primary key (docid)
);
SQL;
        $sql = sprintf($sql, $this->getTableName());
        DbManager::query($sql);
    }

    /**
     * Recompute search data for a specific Smart Element
     * @param SmartElement $se Smart Element to update
     * @param SearchConfig $config Search domain config (for a structure)
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    protected function updateSmartElementIndex(SmartElement $se, $config)
    {
        $data = ["A" => [], "B" => [], "C" => [], "D" => []];
        $fileRequestSend = false;
        $fileRequest = 0;
        foreach ($config->fields as $fieldInfo) {
            if ($fieldInfo->field === "title") {
                // Not use getTitle here because is incomplete data
                $data[$fieldInfo->weight][] = $se->title;
            } else {
                $oa = $se->getAttribute($fieldInfo->field);
                if ($oa === false) {
                    throw new Exception("FSEA0005", $this->domainName, $se, $fieldInfo->field);
                }
                $rawValue = $se->getRawValue($oa->id);
                if (!$rawValue) {
                    continue;
                }
                $data[$fieldInfo->weight][] = SearchSmartElementIndex::getSmartFieldIndex($se, $oa);
            }
        }

        $filesId = [];
        foreach ($config->files as $fileInfo) {
            $oa = $se->getAttribute($fileInfo->field);
            if ($oa === false) {
                throw new Exception("FSEA0005", $this->domainName, $se, $fileInfo->field);
            }
            $rawValue = $se->getRawValue($oa->id);
            if (!$rawValue) {
                continue;
            }


            // Sending files to extract text content by transformation engine
            switch ($oa->type) {
                case 'file':
                    $fileValues = [];
                    if ($oa->isMultiple() === false) {
                        $fileValues[-1] = $rawValue;
                    } else {
                        foreach ($se->getMultipleRawValues($oa->id) as $kf => $rawValue) {
                            $fileValues = $se->getMultipleRawValues($oa->id);
                        }
                    }
                    foreach ($fileValues as $kf => $rawValue) {
                        $fileRequest++;
                        if (preg_match(PREGEXPFILE, $rawValue, $reg)) {
                            if ($reg["vid"]) {
                                $filesId[] = $reg["vid"];
                            }

                            try {
                                $fileRequestSend = IndexFile::sendIndexRequest(
                                    $se,
                                    $this->domainName,
                                    $oa,
                                    $kf
                                ) || $fileRequestSend;
                            } catch (Exception $e) {
                                if ($e->getDcpCode() !== "FSEA0010") {
                                    throw $e;
                                }
                            }
                        }
                    }

                    break;
            }
        }


        foreach ($config->callables as $callInfo) {
            $ft = $callInfo->functionReference;
            if ($ft) {
                $text = $se->applyMethod($ft . "(THIS)");
                if ($text) {
                    $data[$callInfo->weight][] = $text;
                }
            }
        }

        $sql = sprintf(
            "delete from %s where docid=%d; insert into %s (docid, ta, tb, tc, td, files, mdate) values (%d, E'%s', E'%s', E'%s', E'%s',%s, E'%s')",
            $this->getTableName(),
            $se->id,
            $this->getTableName(),
            $se->id,
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["A"]))),
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["B"]))),
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["C"]))),
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["D"]))),
            $filesId ? (pg_escape_literal(Postgres::arrayToString($filesId))) : 'null',
            pg_escape_string(Date::getNow(true))
        );
        DbManager::query($sql);
        if (!$fileRequest) {
            // update vector without insert file content
            $this->updateTsVector($se);
        } elseif (!$fileRequestSend) {
            // update vector with  file content extraction (already record)
            $this->updateSmartWithFiles($se);
        }
    }
}
