<?php

namespace Anakeen\Core\SmartStructure;

/**
 * Document attribute enumerate
 *
 * @class DocEnum
 */

use Anakeen\Core\DbManager;
use Anakeen\Core\EnumCustomLocale;
use Anakeen\Core\EnumManager;
use Anakeen\Core\Internal\DbObj;

class DocEnum extends DbObj
{
    public $fields
        = array(
            "name",
            "key",
            "label",
            "parentkey",
            "disabled",
            "eorder"
        );
    /**
     * name of enum
     *
     * @public int
     */
    public $name;

    /**
     * enum value
     *
     * @public string
     */
    public $key;
    /**
     * default label key
     *
     * @public string
     */
    public $label;
    /**
     * order to display list enum items
     *
     * @public string
     */
    public $eorder = 0;
    /**
     * key of parent enum
     *
     * @public int
     */
    public $parentkey;

    public $id_fields
        = array(
            "name",
            "key"
        );
    protected $needChangeOrder = false;
    /**
     * @var bool
     */
    public $disabled;
    public $dbtable = "docenum";

    public $sqlcreate
        = '
create table docenum (
                   name text not null,
                   key text,
                   label text,
                   parentkey text,
                   disabled bool,
                   eorder int);
create index if_docenum on docenum(name);
create unique index i_docenum on docenum(name,  key);
';

    public function postUpdate()
    {
        if ($this->needChangeOrder) {
            $this->shiftOrder($this->eorder);
        }
    }

    public function postInsert()
    {
        if ($this->needChangeOrder) {
            $this->shiftOrder($this->eorder);
        }
    }

    public function preUpdate()
    {
        $this->consolidateOrder();
        return '';
    }

    public function preInsert()
    {
        $this->consolidateOrder();
        return '';
    }

    /**
     * get last order
     */
    protected function consolidateOrder()
    {
        if (empty($this->eorder) || $this->eorder < 0) {
            $sql = sprintf("select max(eorder) from docenum where name = '%s' ", pg_escape_string($this->name));
            DbManager::query($sql, $newOrder, true, true);

            if ($newOrder > 0) {
                $this->eorder = intval($newOrder) + 1;
            } else {
                $this->eorder = 1;
            }
        }
    }

    public function shiftOrder($n)
    {
        if ($n > 0) {
            $sql = sprintf(
                "update docenum set eorder=eorder + 1 where name = '%s'  and key != '%s' and eorder >= %d",
                pg_escape_string($this->name),
                pg_escape_string($this->key),
                $n
            );
            DbManager::query($sql);
            $seqName = uniqid("tmpseqenum");
            $sql = sprintf("create temporary sequence %s;", $seqName);

            $sqlPattern = <<<'SQL'
UPDATE docenum SET eorder = neworder 
from (SELECT *, nextval('%s') as neworder 
   from (select * from docenum where  name='%s'  order by eorder) as tmpz) as w 
   where w.name=docenum.name  and docenum.key=w.key;
SQL;

            $sql .= sprintf($sqlPattern, $seqName, pg_escape_string($this->name));
            DbManager::query($sql);
        }
    }

    public function exists()
    {
        if ($this->name && $this->key !== null) {
            DbManager::query(sprintf(
                "select true from docenum where name='%s'and key='%s'",
                pg_escape_string($this->name),
                pg_escape_string($this->key)
            ), $r, true, true);
            return $r;
        }
        return false;
    }


    public static function getDisabledKeys($name)
    {
        $sql = sprintf("select key from docenum where name='%s' and disabled", pg_escape_string($name));

        DbManager::query($sql, $dKeys, true);
        return $dKeys;
    }

    protected function setOrder($beforeThan)
    {
        $sql = sprintf("SELECT count(*) FROM docenum WHERE name = '%s' ", pg_escape_string($this->name));
        DbManager::query($sql, $count, true, true);
        if ($beforeThan !== null) {
            $sql = sprintf(
                "select eorder from docenum where name = '%s'  and key='%s'",
                pg_escape_string($this->name),
                pg_escape_string($beforeThan)
            );
            DbManager::query($sql, $beforeOrder, true, true);
            if ($beforeOrder) {
                $this->eorder = $beforeOrder;
            } else {
                /* If the next key does not exists, then set order to count + 1 */
                $this->eorder = $count + 1;
            }
        } elseif (empty($this->eorder)) {
            /*
             * If item has no beforeThan and eorder is not set, then we assume it's the last one
             * (there is nothing after him). So, the order is the number of items + 1
            */
            $this->eorder = $count + 1;
        }
    }

    public static function addEnum($name, EnumStructure $enumStruct)
    {

        $enum = new DocEnum("", array(
            $name,
            $enumStruct->key
        ));
        if ($enum->isAffected()) {
            throw new \Anakeen\Exception(sprintf("Enum %s:%s already exists", $name, $enumStruct->key));
        }

        $enum->name = $name;
        $enum->key = $enumStruct->key;
        $enum->label = $enumStruct->label;
        $enum->disabled = ($enumStruct->disabled === true);
        $enum->needChangeOrder = true;
        $enum->eorder = $enumStruct->absoluteOrder;
        if ($enumStruct->orderBeforeThan === null) {
            $enum->setOrder(null);
        } else {
            $enum->setOrder($enumStruct->orderBeforeThan);
        }
        $err = $enum->add();
        if ($err) {
            throw new \Anakeen\Exception(sprintf("Cannot add enum %s:%s : %s", $name, $enumStruct->key, $err));
        }
        if ($enumStruct->localeLabel) {
            foreach ($enumStruct->localeLabel as $lLabel) {
                self::changeLocale($enum->name, $enumStruct->key, $lLabel->lang, $lLabel->label);
            }
        }
    }

    public static function modifyEnum($name, EnumStructure $enumStruct)
    {

        $enum = new DocEnum("", array(
            $name,
            $enumStruct->key
        ));
        if (!$enum->isAffected()) {
            throw new \Anakeen\Exception(sprintf("Enum %s:%s not found", $name, $enumStruct->key));
        }

        $enum->label = $enumStruct->label;
        $enum->disabled = ($enumStruct->disabled === true);
        if ($enum->eorder != $enumStruct->absoluteOrder) {
            $enum->needChangeOrder = true;
            $enum->eorder = $enumStruct->absoluteOrder;
        }
        if ($enumStruct->orderBeforeThan) {
            $enum->setOrder($enumStruct->orderBeforeThan);
        }

        $err = $enum->modify();
        if ($err) {
            throw new \Anakeen\Exception(sprintf("Cannot modify enum %s:%s : %s", $name, $enumStruct->key, $err));
        }
    }

    public static function changeLocale($enumName, $enumId, $lang, $label)
    {
        $enum = EnumManager::getEnums($enumName);

        $e = new EnumCustomLocale($enumName);
        $e->addEntry($enumId, $label, $lang);
    }
}
