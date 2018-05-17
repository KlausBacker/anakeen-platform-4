<?php
/**
 * Profil for family document
 *
 */


namespace Anakeen\SmartStructures\Profiles;

use Anakeen\SmartHooks;

class PFamHooks extends \Anakeen\Core\Internal\SmartElement

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
    

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::PREIMPORT, function () {
            if ($this->getRawValue("dpdoc_famid")) {
                return \ErrorCode::getError('PRFL0202', $this->getRawValue('ba_title'));
            }
            return '';
        });
    }

}
