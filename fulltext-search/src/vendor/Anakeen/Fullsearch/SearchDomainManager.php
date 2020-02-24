<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;

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
}
