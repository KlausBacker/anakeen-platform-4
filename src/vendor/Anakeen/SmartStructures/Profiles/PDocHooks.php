<?php


namespace Anakeen\SmartStructures\Profiles;

class PDocHooks extends \Anakeen\Core\Internal\SmartElement

{
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

    public $defDoctype = 'P';
    public $defProfFamId = FAM_ACCESSDOC;
    

}
