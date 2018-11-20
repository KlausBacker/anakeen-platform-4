<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckCfldid extends CheckData
{
    protected $folderName;
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
     * @return CheckCfldid
     */
    public function check(array $data, &$doc = null)
    {
        $this->folderName = $data[1];
        $this->doc = $doc;
        $this->checkSearch();
        return $this;
    }
    /**
     * check id it is a search
     * @return void
     */
    protected function checkSearch()
    {
        if ($this->folderName) {
            $d = new_doc('', $this->folderName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('CFLD0001', $this->folderName, $this->doc->name));
            } elseif (!is_a($d, \Anakeen\SmartStructures\Search\SearchHooks::class)) {
                $this->addError(ErrorCode::getError('CFLD0002', $this->folderName, $this->doc->name));
            }
        }
    }
}
