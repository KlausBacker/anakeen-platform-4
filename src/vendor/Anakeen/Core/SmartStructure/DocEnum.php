<?php

namespace Anakeen\Core\SmartStructure;

/**
 * Document attribute enumerate
 *
 * @class DocEnum
 */

use Anakeen\Core\DbManager;
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

            $sql .= sprintf(
                "UPDATE docenum SET eorder = neworder from (SELECT *, nextval('%s') as neworder from (select * from docenum where  name='%s'  order by eorder) as tmpz) as w where w.name=docenum.name  and docenum.key=w.key;",
                $seqName,
                pg_escape_string($this->name)
            );

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
            throw new \Dcp\Exception(sprintf("Enum %s:%s already exists", $name, $enumStruct->key));
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
            throw new \Dcp\Exception(sprintf("Cannot add enum %s:%s : %s", $name, $enumStruct->key, $err));
        }

        if ($enumStruct->localeLabel) {
            foreach ($enumStruct->localeLabel as $lLabel) {
                self::changeLocale($name, $enumStruct->key, $lLabel->lang, $lLabel->label);
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
            throw new \Dcp\Exception(sprintf("Enum %s:%s not found", $name, $enumStruct->key));
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
            throw new \Dcp\Exception(sprintf("Cannot modify enum %s:%s : %s", $name, $enumStruct->key, $err));
        }
        if ($enumStruct->localeLabel) {
            foreach ($enumStruct->localeLabel as $lLabel) {
                self::changeLocale($name, $enumStruct->key, $lLabel->lang, $lLabel->label);
            }
        }
    }

    public static function getMoFilename($enumName, $lang)
    {

        $moFile = sprintf("%s/locale/%s/LC_MESSAGES/customFamily_%s.mo", DEFAULT_PUBDIR, substr($lang, 0, 2), $enumName);
        return $moFile;
    }


    public static function changeLocale($enumName, $enumId, $lang, $label)
    {
        \Anakeen\Core\ContextManager::setLanguage($lang);
        $docenum = new DocEnum("", [$enumName, $enumId]);
        if (!$docenum->isAffected()) {
            throw new \Dcp\Exception(sprintf("Locale : Enum %s:%s not found", $enumName, $enumId));
        }
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
         */
        EnumManager::resetEnum();
        //$curLabel = $oa->getEnumLabel($enumId);
        if ($label !== null) {
            $moFile = self::getMoFilename($enumName, $lang);
            $poFile = sprintf("%s.po", (substr($moFile, 0, -3)));

            $msgInit = sprintf('msgid ""
msgstr ""
"Project-Id-Version: Custom enum for %s\n"
"Language: %s\n"
"PO-Revision-Date: %s"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"', $enumName, substr($lang, 0, 2), date('Y-m-d H:i:s'));
            if (file_exists($moFile)) {
                // Just test mo validity
                $cmd = sprintf("(msgunfmt %s > %s) 2>&1", escapeshellarg($moFile), escapeshellarg($poFile));

                exec($cmd, $output, $ret);
                if ($ret) {
                    throw new \Dcp\Exception(sprintf("Locale : Enum %s:%s error : %s", $enumName, $enumId, implode(',', $output)));
                }
            } else {
                file_put_contents($poFile, $msgInit);
            }
            // add new entry
            $msgEntry = sprintf(
                'msgctxt "%s"'."\n".'msgid "%s"' . "\n" . 'msgstr "%s"',
                str_replace('"', '\\"',$enumName),
                str_replace('"', '\\"', $enumId),
                str_replace('"', '\\"', $label)
            );
            $content = file_get_contents($poFile);
            // fuzzy old entry
            $match = sprintf('msgid "%s"', $enumId);
            $content = str_replace($match, "#, fuzzy\n$match", $content);
            // delete previous header
            $content = str_replace('msgid ""', "#, fuzzy\nmsgid \"- HEADER DELETION -\"", $content);

            file_put_contents($poFile, $msgInit . $msgEntry . "\n\n" . $content);
            $cmd = sprintf("(msguniq --use-first %s | msgfmt - -o %s; rm -f %s) 2>&1", escapeshellarg($poFile), escapeshellarg($moFile), escapeshellarg($poFile));
            exec($cmd, $output, $ret);
            if ($ret) {
                print $cmd;
                throw new \Dcp\Exception(sprintf("Locale : Enum %s:%s error : %s", $enumName, $enumId, implode(',', $output)));
            }
        }
    }
}
