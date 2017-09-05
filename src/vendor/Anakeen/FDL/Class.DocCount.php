<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Cache table to memorize count doc of different sql filter
 *
 * @author Anakeen
 * @version $Id: Class.DocCount.php,v 1.1 2008/08/13 15:17:07 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("Class.DbObj.php");

class DocCount extends DbObj
{
    public $fields = array(
        "famid", // family identifier
        "aid", // attribute identifier
        "filter", // sql filter
        "c", // count
        
    );
    /**
     * family identifier
     * @public string
     */
    public $famid;
    /**
     * attribute identifier
     * @public string
     */
    public $aid;
    /**
     * sql filter of the query
     * @public string
     */
    public $filter;
    /**
     * count result
     * @public int
     */
    public $c;
    
    public $id_fields = array(
        "famid",
        "aid",
        "filter"
    );
    
    public $dbtable = "doccount";
    
    public $sqlcreate = "
create table doccount ( famid int not null,   
                   aid text not null,                    
                   filter text not null,
                   c int  );
create index i_doccount on doccount(famid,aid);
";
    
    function deleteAll()
    {
        $sql = sprintf("delete from %s where famid = %s and aid = '%s'", $this->dbtable, $this->famid, pg_escape_string($this->aid));
        
        return $this->exec_query($sql);
    }
}
?>