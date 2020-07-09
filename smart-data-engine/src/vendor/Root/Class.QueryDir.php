<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Folder managing
 *
 */

/**
 * Folder managing
 *
 * @package FDL
 *
 */
class QueryDir extends \Anakeen\Core\Internal\DbObj
{
    public $fields
        = array(
            "dirid",
            "query",
            "childid",
            "qtype",
            "fromid",
            "doctype"
        );
    /*public $sup_fields= array("fromid",
     "doctype"); */
    // not be in fieldsset by trigger
    public $id_fields
        = array(
            "dirid",
            "childid"
        );

    public $dirid;
    public $query;
    public $childid;
    public $fromid;
    public $qtype;
    public $doctype;
    public $dbtable = "fld";

    public $order_by = "dirid";

    public $fulltextfields
        = array(
            ""
        );

    public $sqlcreate = "
create table fld ( 
                    dirid   int not null,
                    query   text,
                    childid   int,
                    qtype   char,
                    fromid int,
                    doctype char
                   );
create index fld_iqd on fld(qtype,dirid);
create index fld_idir on fld(dirid);
create index fld_iqc on fld(qtype,childid);
create unique index fld_u on fld(qtype,dirid,childid);
create unique index fld_dc on fld(dirid,childid);
create sequence seq_id_fld start 100;
CREATE TRIGGER tfldfrom before insert on fld FOR EACH ROW execute procedure fromfld();";

    #CREATE TRIGGER tfldrel after insert or update or delete on fld FOR EACH ROW execute procedure relfld();";
    public function preInsert()
    {
        // test if not already exist
        if ($this->qtype != "M") {
            $this->delete(false); // delete before insert
        }
    }

    public function exists()
    {
        // test if  already exist
        if ($this->qtype != "M") {
            $this->query(sprintf("select * from fld where dirid=%s and childid=%s", $this->dirid, $this->childid));
            if ($this->numrows() > 0) {
                return true; // just to say it is not a real error
            }
        }
        return false;
    }
}
