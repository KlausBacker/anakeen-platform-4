<?php

namespace Anakeen\Fullsearch;

require_once __DIR__ . "/lib/vendor/autoload.php";

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
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

    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
        $this->domain = new SearchDomain($this->domainName);
    }

    /**
     * Initialize and (re)index all data described in search domain
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public function initialize()
    {
        $sql = sprintf("create schema if not exists %s ", pg_escape_identifier(self::dbSchema));

        DbManager::query($sql);

        $this->createTable();
        $this->createIndex();
        $this->resetData();
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
            if (! is_a($se, SEManager::getFamilyClassName($structureName))) {
                continue;
            }

            $weightFiles = ["A" => [], "B" => [], "C" => [], "D" => []];
            foreach ($config->fields as $fieldInfo) {
                $oa = $se->getAttribute($fieldInfo->field);
                if ($oa === false) {
                    continue;
                }
                $rawValue = $se->getRawValue($oa->id);
                if (!$rawValue) {
                    continue;
                }
                switch ($oa->type) {
                    case 'file':
                        if (is_a($fieldInfo, SearchFileConfig::class)) {
                            /** @var SearchFileConfig $fieldInfo */
                            if ($fieldInfo->filecontent === true) {
                                if ($oa->isMultiple() === false) {
                                    if (preg_match(PREGEXPFILE, $rawValue, $reg)) {
                                        if ($reg["vid"]) {
                                            $weightFiles[$fieldInfo->weight][] = $reg["vid"];
                                        }
                                    }
                                } else {
                                    $rawValues=$se->getMultipleRawValues($oa->id);
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
                        break;
                }
            }


            $sql = <<<SQL
update searches.%s as s set v = 

setweight(to_tsvector('%s', unaccent(ta)), 'A') || 
setweight(to_tsvector('%s', unaccent(tb)), 'B') || 
setweight(to_tsvector('%s', unaccent(tc)), 'C') || 
setweight(to_tsvector('%s', unaccent(td)), 'D') ||

%s

where s.id = %d
SQL;
            $sqlset = [];
            foreach ($weightFiles as $weight => $fileFields) {
                if ($fileFields) {
                    $sqlset[] = sprintf(
                        "setweight((select to_tsvector('%s', unaccent(string_agg(textcontent, ', '))) from %s where fileid in (%s)), '%s')",
                        $this->domain->stem,
                        FileContentDatabase::DBTABLE,
                        implode(", ", $fileFields),
                        pg_escape_string($weight)
                    );
                }
            }

            $updSql=sprintf(
                $sql,
                $this->domain->name,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                $this->domain->stem,
                implode(' || ', $sqlset),
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
                        "plainto_tsquery('%s', unaccent('%s')) as q%d",
                        pg_escape_string($stem),
                        pg_escape_string($part["value"]),
                        $k
                    );
                    break;
                case "PHRASE":
                    $toQuery[] = sprintf(
                        "phraseto_tsquery('%s', unaccent('%s')) as q%d",
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
                    $finalQueryParts[] = sprintf("%s:* ", Strings::unaccent(trim($part["value"], " *")));
                    $needAddAndOperator = true;
            }
        }

        return implode("", $finalQueryParts);
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
            if ($smartStructureSearchconfig->structure === $se->fromname) {
                $config = $smartStructureSearchconfig;
                break;
            }
        }
        if (!$config) {
            throw new Exception("FSEA0006", $this->domainName, $se->fromname);
        }
        // @TODO need delete other revision also if search config has no revision option
        $sql = sprintf(
            "delete from %s where id=%d",
            $this->getTableName(),
            $se->id
        );
        DbManager::query($sql);
        $this->updateSmartElementIndex($se, $config);
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
  id int references docread(id),
  ta text default '',
  tb text default '',
  tc text default '',
  td text default '',
  v tsvector,
  primary key (id)
);
SQL;
        $sql = sprintf($sql, $this->getTableName());
        DbManager::query($sql);
    }


    protected function resetData()
    {
        $currentLanguage = ContextManager::getLanguage();
        if ($this->domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($this->domain->lang);
        }

        $sql = sprintf("delete from %s", $this->getTableName());
        DbManager::query($sql);

        $configs = $this->domain->configs;
        foreach ($configs as $smartStructureSearchconfig) {
            $fields = array_map(function ($item) {
                /** @var SearchFieldConfig $item */
                return $item->field;
            }, $smartStructureSearchconfig->fields);

            $structureName = $smartStructureSearchconfig->structure;
            $structure = SEManager::getFamily($structureName);
            if (!$structure) {
                throw new Exception("FSEA0003", $this->domainName, $structureName);
            }
            $s = new SearchElements($structure->id);
            $s->returnsOnly($fields);
            $results = $s->getResults();

            foreach ($results as $se) {
                $this->updateSmartElementIndex($se, $smartStructureSearchconfig);
            }
        }


        $sql = <<<SQL
update %s set v = 
setweight(to_tsvector('%s', unaccent(ta)), 'A') || 
setweight(to_tsvector('%s', unaccent(tb)), 'B') || 
setweight(to_tsvector('%s', unaccent(tc)), 'C') || 
setweight(to_tsvector('%s', unaccent(td)), 'D');
SQL;
        DbManager::query(sprintf(
            $sql,
            $this->getTableName(),
            $this->domain->stem,
            $this->domain->stem,
            $this->domain->stem,
            $this->domain->stem
        ));

        if ($this->domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($currentLanguage);
        }
    }




    /**
     * @param SmartElement $se
     * @param SearchConfig $config
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    protected function updateSmartElementIndex(SmartElement $se, $config)
    {
        $data = ["A" => [], "B" => [], "C" => [], "D" => []];
        $fileRequestSend=false;
        $fileRequest=0;
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
                switch ($oa->type) {
                    case "timestamp":
                    case "date":
                        $dateFormat = "%A %d %B %Y %m";
                        if ($oa->type === "timestamp") {
                            $dateFormat .= " %H:%M:%S";
                        }
                        if ($oa->isMultiple() === false) {
                            $data[$fieldInfo->weight][] = strftime($dateFormat, strtotime($rawValue));
                        } else {
                            $rawValues = $se->getMultipleRawValues($oa->id);
                            foreach ($rawValues as $rawValue) {
                                $data[$fieldInfo->weight][] = strftime($dateFormat, strtotime($rawValue));
                            }
                        }
                        break;
                    case "enum":
                        if ($oa->isMultiple() === false) {
                            $data[$fieldInfo->weight][] = str_replace("/", " ", $oa->getEnumLabel($rawValue));
                        } else {
                            $rawValues = \Anakeen\Core\Utils\Postgres::stringToFlatArray($rawValue);
                            foreach ($rawValues as $rawValue) {
                                $data[$fieldInfo->weight][] = str_replace(
                                    "/",
                                    " ",
                                    $oa->getEnumLabel($rawValue)
                                );
                            }
                        }
                        break;
                    case "account":
                    case "docid":
                        $docRevOption = $oa->getOption("docrev", "latest");

                        if ($oa->isMultiple() === false) {
                            $data[$fieldInfo->weight][] = \DocTitle::getRelationTitle(
                                $rawValue,
                                $docRevOption === "latest",
                                $se,
                                $docRevOption
                            );
                        } else {
                            $rawValues = \Anakeen\Core\Utils\Postgres::stringToFlatArray($rawValue);
                            foreach ($rawValues as $rawValue) {
                                $data[$fieldInfo->weight][] = \DocTitle::getRelationTitle(
                                    $rawValue,
                                    $docRevOption === "latest",
                                    $se,
                                    $docRevOption
                                );
                            }
                        }
                        break;
                    case 'file':
                        // @TODO Switch to file config field : filename, content, filetype
                        if (is_a($fieldInfo, SearchFileConfig::class)) {

                            /** @var SearchFileConfig $fieldInfo */
                            if ($fieldInfo->filecontent === true) {
                                if ($oa->isMultiple() === false) {
                                    $fileRequest++;
                                    $fileRequestSend = IndexFile::sendIndexRequest($se, $this->domainName, $fieldInfo) || $fileRequestSend;
                                } else {
                                    foreach ($se->getMultipleRawValues($oa->id) as $kf => $rawValue) {
                                        $fileRequest++;
                                        $fileRequestSend = IndexFile::sendIndexRequest($se, $this->domainName, $fieldInfo, $kf) || $fileRequestSend;
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        if ($oa->isMultiple() === false) {
                            $data[$fieldInfo->weight][] = $rawValue;
                        } else {
                            $data[$fieldInfo->weight][] = implode(", ", $se->getMultipleRawValues($oa->id));
                        }
                }
            }
        }

        $sql = sprintf(
            "insert into %s (id, ta, tb, tc, td) values (%d, E'%s', E'%s', E'%s', E'%s')",
            $this->getTableName(),
            $se->id,
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["A"]))),
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["B"]))),
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["C"]))),
            pg_escape_string(preg_replace('/\s+/', ' ', implode(", ", $data["D"])))
        );
        DbManager::query($sql);

        if ($fileRequest && !$fileRequestSend) {
            $this->updateSmartWithFiles($se);
        }
    }


}
