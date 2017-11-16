<?php
/*
 * @author Anakeen
 * @package FDL
 */


namespace Dcp\Pu;

class TestNd extends \Dcp\Family\Document
{
    
    public function postCreated()
    {
        $err = $this->setValue("tst_shared", \Dcp\Core\DocManager::cache()->isDocumentIdInCache($this->id) ? "yes" : "no");
        return $err;
    }
}
