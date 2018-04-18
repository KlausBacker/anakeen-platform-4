<?php
/**
 * Profile for folders
 *
 */


namespace Anakeen\SmartStructures\Profiles;

class PDirHooks extends \DocCollection
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    public $acls = array(
        "view",
        "edit",
        "delete",
        "open",
        "modify",
        "send",
        "unlock",
        "confidential"
    );
    // --------------------------------------------------------------------
    public $defDoctype = 'P';
    public $defProfFamId = FAM_ACCESSDIR;
    
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use \Anakeen\Core\Internal\SmartElement constructor because it could call this constructor => infinitive loop
        \Anakeen\Core\Internal\SmartElement::__construct($dbaccess, $id, $res, $dbid);
    }
}
