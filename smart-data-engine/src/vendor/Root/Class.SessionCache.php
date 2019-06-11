<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Cache session date of validated
 *
 * @author Anakeen
 * @version $Id: Class.SessionCache.php,v 1.4 2005/06/28 13:53:24 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */


class SessionCache extends DbObj
{
    public $fields = array(
        "index",
        "lasttime"
    );
    
    public $id_fields = array(
        "index"
    );
    
    public $dbtable = "session_cache";
    
    public $sqlcreate = "create table session_cache ( index varchar(100), 
			    lasttime	    int);";
    
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        if ((!$this->isAffected()) && ($id != '')) {
            $this->index = $id;
            
            $date = gettimeofday();
            $this->lasttime = $date['sec'];
            $this->add();
        }
    }
    // modify with current date
    public function setTime()
    {
        $date = gettimeofday();
        $this->lasttime = $date['sec'];
        $this->Modify();
    }
}
