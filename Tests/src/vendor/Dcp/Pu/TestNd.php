<?php
/*
 * @author Anakeen
 * @package FDL
 */


namespace Dcp\Pu;

class TestNd extends \Anakeen\SmartStructures\Document
{
    
    public function postCreated()
    {
        $err = $this->setValue("tst_shared", \Anakeen\Core\SEManager::cache()->isDocumentIdInCache($this->id) ? "yes" : "no");
        return $err;
    }
}
