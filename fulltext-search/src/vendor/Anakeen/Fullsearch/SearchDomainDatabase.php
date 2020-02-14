<?php

namespace Anakeen\Fullsearch;

require_once __DIR__ . "/lib/vendor/autoload.php";

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Core\SEManager;
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

    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
    }

    public function initialize()
    {
        $sql = sprintf("create schema if not exists %s ", pg_escape_identifier(self::dbSchema));

        DbManager::query($sql);

        $this->createTable();
        $this->createIndex();
        $this->resetData();
    }

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
        $domain = new SearchDomain($this->domainName);
        $currentLanguage = ContextManager::getLanguage();
        if ($domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($domain->lang);
        }

        $sql = sprintf("delete from %s", $this->getTableName());
        DbManager::query($sql);

        $fmt = new FormatCollection();
        $fmt->setVerifyAttributeAccess(false);

        $configs = $domain->configs;
        foreach ($configs as $config) {
            $fields = array_map(function ($item) {
                /** @var SearchFieldConfig $item */
                return $item->field;
            }, $config->fields);

            $structureName = $config->structure;
            $structure = SEManager::getFamily($structureName);
            if (!$structure) {
                throw new Exception("FSEA0003", $this->domainName, $structureName);
            }
            $s = new SearchElements($structure->id);
            $s->returnsOnly($fields);
            $results = $s->getResults();

            foreach ($results as $se) {
                $data = ["A" => [], "B" => [], "C" => [], "D" => [],];
                foreach ($config->fields as $fieldInfo) {
                    if ($fieldInfo->field === "title") {
                        // Not use getTitle here because is incomplete data
                        $data[$fieldInfo->weight][] = $se->title;
                    } else {
                        $oa = $structure->getAttribute($fieldInfo->field);
                        if ($oa === false) {
                            throw new Exception("FSEA0005", $this->domainName, $structureName, $fieldInfo->field);
                        }
                        $rawValue = $se->getRawValue($oa->id);
                        if (!$rawValue) {
                            continue;
                        }
                        switch ($oa->type) {
                            case "timestamp":
                            case "date":
                                $dateFormat = "%A %d %B %m %Y";
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
            $domain->stem,
            $domain->stem,
            $domain->stem,
            $domain->stem
        ));

        if ($domain->lang !== $currentLanguage) {
            ContextManager::setLanguage($currentLanguage);
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

        $config->addTokenDefinition(new TokenDefn("NOT", '\\s-', "u"));
        $config->addTokenDefinition(new TokenDefn("OR", '\\sor\\s', "u"));
        $config->addTokenDefinition(new TokenDefn("PHRASE", '"[^"]+"', "u"));
        $config->addTokenDefinition(new TokenDefn("SPACES", "\\s+", "u"));
        $config->addTokenDefinition(new TokenDefn("PLAIN", "[^\\s]+", "u"));

        // lexer instance
        $lexer = new Lexer($config);

        $lexer->setInput($pattern);
        $lexer->moveNext();

        $parts = [];
        $currentSequence = "";
        while ($lexer->getLookahead()) {
            $tokenName = $lexer->getLookahead()->getName();
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
                        $part["value"],
                        $k
                    );
                    break;
                case "PHRASE":
                    $toQuery[] = sprintf(
                        "phraseto_tsquery('%s', unaccent('%s')) as q%d",
                        pg_escape_string($stem),
                        trim($part["value"], '"'),
                        $k
                    );
                    break;
            }
        }

        $sql = sprintf("select %s", implode(", ", $toQuery));
        DbManager::query($sql, $queryResults, false, true);

        $finalQueryParts = [];
        $previousWords = false;
        foreach ($parts as $k => $part) {
            switch ($part["token"]) {
                case "PHRASE":
                case "PLAIN":
                    if (!empty($queryResults["q$k"])) {
                        if ($previousWords === true) {
                            $finalQueryParts[] = " & ";
                        }

                        $previousWords = true;
                        $finalQueryParts[] = sprintf("(%s)", $queryResults["q$k"]);
                    }
                    break;
                case "OR":
                    $finalQueryParts[] = " | ";
                    $previousWords = false;
                    break;
                case "NOT":
                    $finalQueryParts[] = " & !";
                    $previousWords = false;
                    break;
            }
        }

        return implode("", $finalQueryParts);
    }
}
