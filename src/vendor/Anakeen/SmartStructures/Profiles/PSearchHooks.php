<?php

namespace Anakeen\SmartStructures\Profiles;

class PSearchHooks extends \DocCollection
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
        // don't use \Anakeen\Core\Internal\SmartElement constructor because it could call this constructor => infinitive loop
        \Anakeen\Core\Internal\SmartElement::__construct($dbaccess, $id, $res, $dbid);
    }
}
