<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckDfldid extends CheckData
{
    protected $folderName;
    /**
     * @var \Anakeen\Core\Internal\SmartElement 
     */
    protected $doc;
    

    /**
     * @param array $data
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return CheckDfldid
     */
    public function check(array $data, &$doc = null)
    {
        $this->folderName = isset($data[1]) ? $data[1] : null;
        $this->doc = $doc;
        $this->checkFolder();
        return $this;
    }
    /**
     * check if it is a folder
     * @return void
     */
    protected function checkFolder()
    {
        if ($this->folderName && $this->folderName != 'auto') {
            $d = Anakeen\Core\DocManager::getDocument($this->folderName);
            if (!$d || !$d->isAlive()) {
                $this->addError(ErrorCode::getError('DFLD0001', $this->folderName, $this->doc->name));
            } elseif (!is_a($d, \Anakeen\SmartStructures\Dir\DirHooks::class)) {
                $this->addError(ErrorCode::getError('DFLD0002', $this->folderName, $this->doc->name));
            }
        }
    }
}
