<?php

class CheckCfallid extends CheckData
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
     * @param array                               $data
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return CheckCfallid
     */
    public function check(array $data, &$doc = null)
    {
        $this->profilName = $data[1];
        $this->doc = $doc;
        $this->checkFallid();
        return $this;
    }

    /**
     * check id it is a profil
     * @return void
     */
    protected function checkFallid()
    {
        if ($this->profilName) {
            $d = \Anakeen\Core\SEManager::getDocument($this->profilName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('CPRF0003', $this->profilName, $this->doc->name));
            } elseif (!is_a($d, \SmartStructure\FieldAccessLayerList::class)) {
                $this->addError(ErrorCode::getError('CPRF0004', $this->profilName, $this->doc->name));
            }
        }
    }
}
