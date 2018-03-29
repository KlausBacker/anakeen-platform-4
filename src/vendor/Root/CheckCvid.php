<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckCvid extends CheckData
{
    protected $folderName;
    /**
     * @var Doc
     */
    protected $doc;


    /**
     * @param array $data
     * @param Doc   $doc
     *
     * @return CheckCvid
     */
    public function check(array $data, &$doc = null)
    {
        $this->folderName = $data[1];
        $this->doc = $doc;
        $this->checkCv();
        return $this;
    }

    /**
     * check id it is a search
     *
     * @return void
     */
    protected function checkCv()
    {
        if ($this->folderName) {
            $d = \Anakeen\Core\DocManager::getDocument($this->folderName);

            if (!$d || !$d->isAlive()) {
                $this->addError(ErrorCode::getError('CVID0001', $this->folderName, $this->doc->name));
            } elseif (!is_a($d, \Anakeen\Core\DocManager::getFamilyClassName("CVDOC"))) {
                $this->addError(ErrorCode::getError('CVID0002', $this->folderName, $this->doc->name));
            }
        }
    }
}
