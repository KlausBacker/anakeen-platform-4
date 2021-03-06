<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document Relation Class
 *
 * @author  Anakeen
 * @version $Id: Class.DocRel.php,v 1.13 2008/12/03 13:55:14 eric Exp $
 * @package FDL
 */

/**
 */
class DocRel extends DbObj
{
    public $fields
        = array(
            "sinitid", // source id
            "cinitid", // cible id
            "ctitle", // title of cible
            "cicon", // icon of cible
            "stitle", // title of source
            "sicon", // icon of source
            "type", // relation kind
            "doctype"
        );
    /**
     * identifier of the source document
     *
     * @public int
     */
    public $sinitid;
    /**
     * identifier of the cible document
     *
     * @public int
     */
    public $cinitid;
    /**
     * title of the cible document
     *
     * @public int
     */
    public $title;
    /**
     * relation kind
     *
     * @public int
     */
    public $type;

    public $id_fields
        = array(
            "sinitid"
        );

    public $dbtable = "docrel";
    public $ctitle;
    public $cicon;
    public $stitle;
    public $sicon;
    public $doctype;

    public $sqlcreate
        = "
create table docrel ( sinitid int not null,                   
                   cinitid int not null,
                   stitle text,
                   ctitle text,
                   sicon text,
                   cicon text,
                   type text,
                   doctype text  );
create index i_docrelc on docrel(cinitid);
create index i_docrels on docrel(sinitid);
create unique index docrel_u on docrel(sinitid,cinitid,type);";

    public function getRelations($reltype = "", $doctype = "", $limit = 0)
    {
        if (empty($this->sinitid)) {
            return array();
        }
        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, get_class($this));
        $q->AddQuery(sprintf("sinitid = %d", $this->sinitid));
        if ($reltype != "") {
            $q->AddQuery("type='$reltype'");
        }
        if ($doctype != "") {
            $q->AddQuery("doctype='$doctype'");
        }
        $l = $q->Query(0, $limit, "TABLE");
        if (is_array($l)) {
            return $l;
        }
        return array();
    }

    public function getIRelations($reltype = "", $doctype = "", $limit = 0)
    {
        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, get_class($this));
        $q->AddQuery(sprintf("cinitid= %d", $this->sinitid));
        if ($reltype != "") {
            $q->AddQuery("type='$reltype'");
        }
        if ($doctype != "") {
            $q->AddQuery("doctype='$doctype'");
        }
        $l = $q->Query(0, $limit, "TABLE");
        if (is_array($l)) {
            return $l;
        }
        return array();
    }

    /**
     * Delete document relations
     *
     * @param string $type    a special type of relation . Empty means all relations
     * @param int    $sinitid document identifier of relation (initid)
     *
     * @return void
     */
    public function resetRelations($type = "", int $sinitid = 0)
    {
        if ($sinitid === 0) {
            $sinitid = $this->sinitid;
        }
        if ($sinitid > 0) {
            if ($type != "") {
                $this->query("delete from docrel where sinitid=" . $sinitid . " and type='$type'");
            } else {
                $this->query("delete from docrel where sinitid=" . $sinitid . " and type != 'folder'");
            }
        }
    }

    /**
     * Update document relations
     *
     * @param \Anakeen\Core\Internal\SmartElement &$doc  document to initialize relations
     * @param bool                                $force if force recomputing
     *
     * @return void
     */
    public function initRelations(&$doc, $force = false)
    {
        $nattr = $doc->GetNormalAttributes();

        $savePoint = uniqid("dcp:initrelation");

        \Anakeen\Core\DbManager::savePoint($savePoint);
        \Anakeen\Core\DbManager::lockPoint($doc->initid, "IREL"); // need to avoid conflict in docrel index
        foreach ($nattr as $k => $v) {
            if (isset($doc->$k) && ($v->type == "docid" || $v->type == "account")) {
                if (!$force) {
                    if ($doc->getOldRawValue($v->id) === false) {
                        continue;
                    }
                }
                // reset old relations
                pg_query($this->dbid, sprintf("delete from docrel where sinitid=%d and type='%s'", $doc->initid, pg_escape_string($v->id)));
                if ($v->isMultiple()) {
                    $tv = array_unique(\Anakeen\Core\Utils\Postgres::stringToFlatArray($doc->$k));
                } else {
                    $tv = array($doc->$k);
                }
                $tvrel = array();
                foreach ($tv as $relid) {
                    if (is_numeric($relid)) {
                        $tvrel[] = intval($relid);
                    }
                }
                $tvrel = array_unique($tvrel);
                $this->copyRelations($tvrel, $doc, $v->id);
            }
        }
        \Anakeen\Core\DbManager::commitPoint($savePoint);
    }

    /**
     * copy in db document relations
     *
     * @param array                               &$tv  array of docid
     * @param \Anakeen\Core\Internal\SmartElement &$doc document source
     *
     * @return void
     */
    public function copyRelations(&$tv, &$doc, $reltype)
    {
        $tv = array_filter($tv, function ($a) {
            return (!empty($a));
        });
        if (count($tv) > 0) {
            // increase speed using pg_copy
            $sql = sprintf(
                "select distinct on (initid, icon, title) initid, icon, title from docread where initid in (SELECT initid from docread where %s) and locked != -1;",
                \Anakeen\Core\DbManager::getSqlOrCond($tv, 'id', true)
            );

            $this->query($sql);
            if ($this->numrows() > 0) {
                $c = 0;
                $tin = array();
                while ($row = @pg_fetch_array($this->res, $c, PGSQL_ASSOC)) {
                    $tin[] = sprintf(
                        "%d\t%d\t%s\t%s\t%s\t%s\t%s\t%s",
                        $doc->initid,
                        $row["initid"],
                        $this->escapePgCopyChars($doc->title),
                        $this->escapePgCopyChars($row["title"]),
                        $doc->icon,
                        $row["icon"],
                        $reltype,
                        $doc->doctype
                    );
                    $c++;
                }

                pg_copy_from($this->dbid, "docrel", $tin);
            }
        }
    }

    protected function escapePgCopyChars($str)
    {
        /* Escape literal backslash chars */
        $str = str_replace("\\", "\\\\", $str);
        /* These characters should also be escaped, but for compatibility reasons we will simply replace them with a space char */
        $str = str_replace("\r", " ", $str);
        $str = str_replace("\n", " ", $str);
        $str = str_replace("\t", " ", $str);
        return $str;
    }
}
