<?php


namespace Anakeen\SmartStructures\Profiles;

class PDocHooks extends \Anakeen\Core\Internal\SmartElement

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
        // don't use \Anakeen\Core\Internal\SmartElement constructor because it could call this constructor => infinitive loop
        \Anakeen\Core\Internal\SmartElement::__construct($dbaccess, $id, $res, $dbid);
    }
}
