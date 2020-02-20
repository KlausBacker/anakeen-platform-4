<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;

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
        $domainsParam = ContextManager::getParameterValue(self::NSPARAM, "DOMAIN_CONFIG");
        if ($domainsParam) {
            $domains = json_decode($domainsParam, true);
        } else {
            $domains = [];
        }
        $domains=self::getConfig();
        $domains[$domain->name] = $domain;

        ContextManager::setParameterValue(self::NSPARAM, "DOMAIN_CONFIG", json_encode($domains));
    }
}
