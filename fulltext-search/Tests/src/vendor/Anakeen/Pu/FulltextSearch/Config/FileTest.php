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
            try {
                $searchDomain = new SearchDomain("testDomainFile");
                $searchDomain->reindexSearchDataElement($this);
            } catch (Exception $e) {
                if ($e->getDcpCode() !== 'FSEA0002') {
                    throw $e;
                }
            }

            return "";
        });
    }
}
