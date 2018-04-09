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
        // don't use Doc constructor because it could call this constructor => infinitive loop
        \DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
}
