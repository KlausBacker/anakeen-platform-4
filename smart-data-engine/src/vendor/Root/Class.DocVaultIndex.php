<?php
/**
 * Class.DocVaultIndex.php manages a full index
 * for files attached to a Freedom document
 *
 */


class DocVaultIndex extends \Anakeen\Core\Internal\DbObj
{
    public $fields
        = array(
            "docid",
            "vaultid"
        );

    public $id_fields
        = array(
            "docid",
            "vaultid"
        );

    public $dbtable = "docvaultindex";

    public $order_by = "docid";
    public $docid;
    public $vaultid;
    public $sqlcreate = "
create table docvaultindex ( docid  int not null,
                             vaultid bigint not null
                   );
create index idx_docvid on docvaultindex(vaultid);
create unique index idx_docvaultindex on docvaultindex (docid, vaultid);";

    /**
     * return doc ids from a vault file
     *
     * @param int $vid vault id
     *
     * @return array object
     */
    public function getDocIds($vid)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->basic_elem->sup_where = array(
            "vaultid = $vid"
        );
        $t = $query->Query();

        return $t;
    }

    /**
     * return first doc id from a vault file
     *
     * @param int $vid vault id
     *
     * @return int id of document
     */
    public function getDocId($vid)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->AddQuery("vaultid = $vid");
        $t = $query->Query(0, 1, "TABLE");
        if (is_array($t)) {
            return $t[0]["docid"];
        }
        return false;
    }

    /**
     * return vault ids for a document
     *
     * @param int $docid document id
     *
     * @return array
     */
    public function getVaultIds($docid)
    {
        if (!$docid) {
            return array();
        }
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->AddQuery("docid = $docid");
        $t = $query->Query(0, 0, "TABLE");
        $tvid = array();
        if (is_array($t)) {
            foreach ($t as $tv) {
                $tvid[] = $tv["vaultid"];
            }
        }
        return $tvid;
    }

    public function deleteDoc($docid)
    {
        $err = $this->query("delete from " . $this->dbtable . " where docid=" . $docid);
        return $err;
    }

    public function deleteVaultId($vid)
    {
        $err = $this->query("delete from " . $this->dbtable . " where vaultid=" . $vid);
        return $err;
    }
}
