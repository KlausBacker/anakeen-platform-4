<?php


namespace Anakeen\Pu\FulltextSearch\Config;


use Anakeen\Exception;
use Anakeen\Fullsearch\SearchDomain;
use Anakeen\SmartHooks;

class FileTest extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {

            $file = $this->getOldRawValue("tst_file");
            $files = $this->getOldRawValue("tst_files");

            if ($file !== false || $files !== false) {
                $searchDomain=new SearchDomain("testDomainFile");
                $searchDomain->reindexSearchDataElement($this);
            }

            return "";
        });

    }
}