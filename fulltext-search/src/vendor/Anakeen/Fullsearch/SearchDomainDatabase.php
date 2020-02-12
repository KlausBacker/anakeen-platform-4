<?php

namespace Anakeen\Fullsearch;

use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;

class SearchDomainDatabase
{
    const dbSchema = "searches";

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

        $sql=sprintf("delete from %s", $this->getTableName());
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
                        $info = $fmt->getInfo($oa, $se->getRawValue($oa->id), $se);
                        if ($info === null) {
                            continue;
                        }
                        if (is_array($info) === false) {
                            $data[$fieldInfo->weight][] = $info->displayValue;
                        } else {
                            //  ARRAY MULTIPLE
                            foreach ($info as $item) {
                                if (is_array($item) === false) {
                                    $data[$fieldInfo->weight][] = $item->displayValue;
                                } else {
                                    //  ARRAY MULTIPLE^2
                                    foreach ($item as $datum) {
                                        $data[$fieldInfo->weight][] = $datum->displayValue;
                                    }
                                }
                            }
                        }
                    }
                }

                $sql = sprintf(
                    "insert into %s (id, ta, tb, tc, td) values (%d, E'%s', E'%s', E'%s', E'%s')",
                    $this->getTableName(),
                    $se->id,
                    pg_escape_string(implode(", ", $data["A"])),
                    pg_escape_string(implode(", ", $data["B"])),
                    pg_escape_string(implode(", ", $data["C"])),
                    pg_escape_string(implode(", ", $data["D"]))
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
    }
}
