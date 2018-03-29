<?php
/**
 * Profil for family document
 *
 */


namespace Anakeen\SmartStructures\Profiles;

class PFam extends \Doc
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    public $acls = array(
        "view",
        "edit",
        "create",
        "icreate"
    );
    
    public $defDoctype = 'P';
    public $defProfFamId = FAM_ACCESSFAM;
    
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        \DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
    
    public function preImport(array $extra = array())
    {
        if ($this->getRawValue("dpdoc_famid")) {
            return \ErrorCode::getError('PRFL0202', $this->getRawValue('ba_title'));
        }
        return '';
    }
}
