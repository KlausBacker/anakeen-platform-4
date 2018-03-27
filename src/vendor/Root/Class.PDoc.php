<?php



class PDoc extends Doc
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    public $acls = array(
        "view",
        "edit",
        "delete",
        "send",
        "unlock",
        "confidential",
        "wask"
    );
    // --------------------------------------------------------------------
    // ------------
    public $defDoctype = 'P';
    public $defProfFamId = FAM_ACCESSDOC;
    
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
}
