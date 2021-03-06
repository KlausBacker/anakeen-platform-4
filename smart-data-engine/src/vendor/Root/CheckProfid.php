<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Checking family profid property
 * @class CheckProfid
 * @brief Check family profid property  when importing definition
 * @see ErrorCodePRFD
 */
class CheckProfid extends CheckData
{
    /**
     * profil name
     * @var string
     */
    private $prfName = '';
    /**
     * profil doccument
     * @var \Anakeen\Core\Internal\SmartElement
     */
    private $profil = '';
    /**
     * @param array $data
     * @return CheckProfid
     */
    public function check(array $data, &$extra = null)
    {
        $this->prfName = $data[1];
        $this->checkUnknow();
        if (!$this->hasErrors()) {
            $this->checkIsAFamilyProfil();
        }
        
        return $this;
    }
    
    private function checkUnknow()
    {
        if ($this->prfName) {
            try {
                $this->profil = \Anakeen\Core\SEManager::getDocument($this->prfName);
            } catch (Exception $e) {
                // due to no test validity of the family now
                $fam =  \Anakeen\Core\SEManager::getRawDocument($this->prfName);
                if (!$fam) {
                    throw $e;
                }
                if ($fam["doctype"] === "C") {
                    $this->profil = new \Anakeen\Core\SmartStructure();
                    $this->profil->affect($fam);
                }
            }
            if (!$this->profil || !$this->profil->isAlive()) {
                $this->addError(ErrorCode::getError('PRFD0001', $this->prfName));
            }
        }
    }
    
    private function checkIsAFamilyProfil()
    {
        if (!is_a($this->profil, Anakeen\SmartStructures\Profiles\PFamHooks::class)) {
            $this->addError(ErrorCode::getError('PRFD0002', $this->prfName));
        }
    }
}
