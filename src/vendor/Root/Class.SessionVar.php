<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.SessionVar.php,v 1.3 2003/08/18 15:46:42 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once('Class.DbObj.php');

class SessionVar extends DbObj
{
    public $fields = array(
        "session",
        "key",
        "val"
    );
    
    public $id_fields = array(
        "session",
        "key"
    );
    
    public $dbtable = "session_vars";
    
    public $sqlcreate = "create table session_vars ( session varchar(100), 
			    key	    varchar(50),
			    val	    varchar(200));
create index session_vars_idx on session_vars(session,key);";
}
