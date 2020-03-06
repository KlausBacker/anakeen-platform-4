<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;

class SearchDomainManager
{
    const NSPARAM = "Fullsearch";
    const PARAMCONFIG = "DOMAIN_CONFIG";


    /**
     * Return domain config recorded to context parameter
     * @return array
     */
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


    /**
     * Record domain config into context parameter
     * @param SearchDomain $domain
     */
    public static function recordDomainConfig(SearchDomain $domain)
    {
        $domains=self::getConfig();
        $domains[$domain->name] = $domain;

        ContextManager::setParameterValue(self::NSPARAM, "DOMAIN_CONFIG", json_encode($domains));
    }

    /**
     * Update search data for all Search Domain compatible with a specific smartElement
     * @param SmartElement $smartElement
     */
    public static function updateSmartElementSearchData(SmartElement $smartElement)
    {
        $domains = self::getConfig();

        foreach ($domains as $domainName => $config) {
            $domain = new SearchDomain($domainName);
            try {
                $domain->reindexSearchDataElement($smartElement);
            } catch (Exception $e) {
                if ($e->getDcpCode() !== "FSEA0006") {
                    throw $e;
                }
            }
        }
    }
}
