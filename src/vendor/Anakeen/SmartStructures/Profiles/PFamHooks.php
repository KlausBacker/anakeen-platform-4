<?php
/**
 * Profil for family document
 *
 */


namespace Anakeen\SmartStructures\Profiles;

class PFamHooks extends \Doc
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
    

    
    public function preImport(array $extra = array())
    {
        if ($this->getRawValue("dpdoc_famid")) {
            return \ErrorCode::getError('PRFL0202', $this->getRawValue('ba_title'));
        }
        return '';
    }
}
