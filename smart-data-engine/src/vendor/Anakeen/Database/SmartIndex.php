<?php

namespace Anakeen\Database;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

class SmartIndex
{
    protected $unique = false;

    protected $using = "";
    /** @var string[] */
    protected $columns = [];
    protected $cascade = false;
    protected $id = "";
    /**
     * @var SmartStructure
     */
    protected $smartStructure;

    public function __construct(SmartStructure $structure)
    {
        $this->smartStructure = $structure;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * If the index is unique on the table
     * @param bool $unique
     * @return SmartIndex
     */
    public function setUnique(bool $unique): SmartIndex
    {
        $this->unique = $unique;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Where index is is used : ie in which smart fields
     * @param string[] $columns smart fields names
     * @return SmartIndex
     */
    public function setColumns(array $columns): SmartIndex
    {
        $se = SEManager::initializeDocument($this->smartStructure->id);
        foreach ($columns as $column) {
            if (!in_array($column, $se->fields)) {
                throw new Exception("DB0210", $this->smartStructure->name, $column);
            }
        }
        $this->columns = $columns;
        return $this;
    }

    /**
     * By default btree is used
     * @param string $using
     * @return SmartIndex
     */
    public function setUsing(string $using): SmartIndex
    {
        $this->using = $using;
        return $this;
    }

    public function getId(): string
    {
        return $this->getStructIndexId($this->smartStructure->id);
    }

    /**
     * To set index also on sub sttructure
     * @param bool $cascade
     * @return SmartIndex
     */
    public function setCascade(bool $cascade): SmartIndex
    {
        $this->cascade = $cascade;
        return $this;
    }

    public function __toString()
    {
        $ids = $this->getStructIds();
        $sqls = [];
        foreach ($ids as $child) {
            $sqls[] = $this->getCreateSql($child);
        }
        return implode("\n", $sqls);
    }

    /**
     * Record the index if not exists
     * @throws Exception
     */
    public function create()
    {
        if (!$this->columns) {
            throw new Exception("DB0211", $this->smartStructure->name);
        }
        DbManager::query($this->__toString());
    }

    public function exists()
    {
        return !empty($this->getInfo());
    }

    /**
     * Example of return info
     *
     *  [schemaname] => public
     *  [tablename] => doc7
     *  [indexname] => fi_titlew_fi_title7
     *  [tablespace] =>
     *  [indexdef] => CREATE INDEX fi_titlew_fi_title7 ON public.doc7 USING btree (fi_titlew, fi_title)
     * @return array system info from database indexes (empty if not found)
     * @throws Exception
     */
    public function getInfo()
    {
        $ids = $this->getStructIds();
        $info=[];
        foreach ($ids as $id) {
            $sql = sprintf(
                "select * from pg_indexes where indexname = '%s'",
                pg_escape_string($this->getStructIndexId($id))
            );
            DbManager::query($sql, $result, false, true);
            $info[]= $result;
        }
        return $info;
    }

    /**
     * Drop the index if exists
     * @throws Exception
     */
    public function drop()
    {
        $ids = $this->getStructIds();
        $sqls = [];
        foreach ($ids as $child) {
            $sqls[] = sprintf(
                "drop index if exists %s;",
                pg_escape_identifier($this->getStructIndexId($child))
            );
        }
        DbManager::query(implode("\n", $sqls));
    }

    /**
     * Reindex database index
     * @throws Exception
     */
    public function reindex()
    {
        $ids = $this->getStructIds();
        $sqls = [];
        foreach ($ids as $child) {
            $sqls[] = sprintf(
                "reindex index %s;",
                pg_escape_identifier($this->getStructIndexId($child))
            );
        }
        DbManager::query(implode("\n", $sqls));
    }

    /**
     * Add specific id (must be unique)
     * If not set , id is computed from column keys
     * In any case, the database id is the concatenation of the id and the id number of Smart Structure
     * @param string $id
     * @return SmartIndex
     */
    public function setId(string $id): SmartIndex
    {
        $this->id = $id;
        return $this;
    }

    protected function getStructIds(): array
    {
        $ids = [$this->smartStructure->id];
        if ($this->cascade) {
            $childs = array_keys($this->smartStructure->getChildFam());
            $ids = array_merge($ids, $childs);
        }
        return $ids;
    }

    protected function getStructIndexId($id): string
    {
        return ($this->id ?: implode("_", $this->columns)) . $id;
    }

    protected function getCreateSql(int $structureId)
    {
        return sprintf(
            "create%sindex if not exists %s on doc%d%s(%s);",
            $this->unique ? " unique " : " ",
            pg_escape_identifier($this->getStructIndexId($structureId)),
            $structureId,
            $this->using ? (" using " . $this->using) : "",
            implode(",", $this->espaceFields($this->columns))
        );
    }

    private function espaceFields(array $names)
    {
        return array_map(function ($item) {
            return pg_escape_identifier($item);
        }, $names);
    }
}
