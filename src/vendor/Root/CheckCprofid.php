<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckCprofid extends CheckData
{
    protected $profilName;
    /**
     * @var \Anakeen\Core\Internal\SmartElement 
     */
    protected $doc;
    
    protected $authorizedKeys = array(
        "attributes"
    );
    /**
     * @param array $data
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return CheckCprofid
     */
    public function check(array $data, &$doc = null)
    {
        $this->profilName = $data[1];
        $this->doc = $doc;
        $this->checkProfil();
        return $this;
    }
    /**
     * check id it is a search
     * @return void
     */
    protected function checkProfil()
    {
        if ($this->profilName) {
            $d = new_doc('', $this->profilName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('CPRF0001', $this->profilName, $this->doc->name));
            } elseif (!is_a($d, \Anakeen\Core\Internal\SmartElement::class)) {
                $this->addError(ErrorCode::getError('CPRF0002', $this->profilName, $this->doc->name));
            }
        }
    }
}
