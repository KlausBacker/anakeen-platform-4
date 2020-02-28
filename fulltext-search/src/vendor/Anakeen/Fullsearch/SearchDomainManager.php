<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;

class SearchDomainManager
{
    const NSPARAM = "Fullsearch";
    const PARAMCONFIG = "DOMAIN_CONFIG";


    public static function getConfig()
    {
        $domainsParam = ContextManager::getParameterValue(self::NSPARAM, "DOMAIN_CONFIG");

        if ($domainsParam) {
            $domains = json_decode($domainsParam, true);
        } else {
            $domains = [];
        }
        return $domains;
    }


    public static function recordDomainConfig(SearchDomain $domain)
    {
        $domains=self::getConfig();
        $domains[$domain->name] = $domain;

        ContextManager::setParameterValue(self::NSPARAM, "DOMAIN_CONFIG", json_encode($domains));
    }

    /**
     * Update search data for all Search Domain compatibl with a specific smartElement
     * @param SmartElement $smartElement
     */
    public static function updateSmartElementSearchData(SmartElement $smartElement)
    {
        $domains = self::getConfig();

        foreach ($domains as $domainName => $config) {
            $domain = new SearchDomain($domainName);
            $domain->reindexSearchDataElement($smartElement);
        }
    }
}
