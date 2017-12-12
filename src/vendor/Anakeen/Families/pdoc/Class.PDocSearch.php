<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.PDocSearch.php,v 1.9 2006/04/03 14:56:26 eric Exp $
 * @package FDL
 */
/**
 */

$CLASS_CONTACT_PHP = '$Id: Class.PDocSearch.php,v 1.9 2006/04/03 14:56:26 eric Exp $';

include_once("FDL/Class.DocCollection.php");

class PDocSearch extends DocCollection
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    public $acls = array(
        "view",
        "edit",
        "delete",
        "execute",
        "unlock",
        "confidential"
    );
    // --------------------------------------------------------------------
    public $defDoctype = 'P';
    public $defProfFamId = FAM_ACCESSSEARCH;
    
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
}
