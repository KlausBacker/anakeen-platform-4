<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Virtual groups
 *
 * @author Anakeen
 * @version $Id: Class.VGroup.php,v 1.2 2004/02/12 10:32:09 eric Exp $
 * @package FDL
 */
/**
 */

/**
 * Virtual groups
 * @package FDL
 *
 */
class VGroup extends DbObj
{
    const STARTIDVGROUP=1000000;
    public $fields = array(
        "id",
        "num"
    );
    
    public $id_fields = array(
        "id"
    );
    
    public $id;
    public $num;
    public $dbtable = "vgroup";
    
    public $order_by = "id";
    
    public $sqlcreate = "
create table vgroup ( id  text primary key,
                      num int not null);
create sequence seq_id_docvgroup start 1000000;";
    
    public function PreInsert()
    {
        // compute new id
        if ($this->num == "") {
            $res = pg_query($this->dbid, "select nextval ('seq_id_docvgroup')");
            $arr = pg_fetch_array($res, 0);
            $this->num = $arr[0]; // not a number must be alphanumeric begin with letter
        }
    }
}
