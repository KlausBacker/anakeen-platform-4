<?php
/*
 * @author Anakeen
 * @package FDL
 */


namespace Dcp\Pu;

use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;

class TestNd extends \Anakeen\SmartStructures\Document
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTCREATED, function () {
            return $this->setShared();
        });
    }
    protected function setShared()
    {
        SEManager::cache()->addDocument($this);
        $err = $this->setValue("tst_shared", \Anakeen\Core\SEManager::cache()->isDocumentIdInCache($this->id) ? "yes" : "no");
        $err.=$this->setValue("tst_data", "nd creation");
        return $err;
    }
    public function postRevise()
    {
        SEManager::cache()->addDocument($this);
        $err = $this->setValue("tst_shared", \Anakeen\Core\SEManager::cache()->isDocumentIdInCache($this->id) ? "yes" : "no");
        $err.=$this->setValue("tst_data", "nd revision");
        return $err;
    }


}
