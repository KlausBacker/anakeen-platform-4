<?php
/*
 * @author Anakeen
 * @package FDL
 */


namespace Dcp\Pu;

class TestNd extends \SmartStructure\Document
{
    
    public function postCreated()
    {
        $err = $this->setValue("tst_shared", \Anakeen\Core\DocManager::cache()->isDocumentIdInCache($this->id) ? "yes" : "no");
        return $err;
    }
}
