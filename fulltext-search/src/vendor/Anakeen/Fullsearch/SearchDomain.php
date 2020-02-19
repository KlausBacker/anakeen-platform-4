<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;

class SearchDomain implements \JsonSerializable
{
    const NSPARAM = "Fullsearch";
    public $name;
    public $stem;
    /** @var SearchConfig[] */
    public $configs = [];
    /**
     * @var string
     */
    public $lang;

    public function __construct(string $name = "")
    {
        if ($name) {
            $this->get($name);
        }
    }

    public function get($name)
    {
        $domainsParam = ContextManager::getParameterValue(self::NSPARAM, "DOMAIN_CONFIG");
        if ($domainsParam) {
            $domains = json_decode($domainsParam, true);
        } else {
            $domains = [];
        }
        if (!isset($domains[$name])) {
            throw new Exception("FSEA0002", $name);
        }

        $domainData = $domains[$name];
        $this->name = $domainData["name"];
        $this->stem = $domainData["stem"];
        $this->lang = $domainData["lang"];
        foreach ($domainData["configs"] as $config) {
            $this->configs[] = new SearchConfig($config);
        }
    }


    public function record()
    {
        $domainsParam = ContextManager::getParameterValue(self::NSPARAM, "DOMAIN_CONFIG");
        if ($domainsParam) {
            $domains = json_decode($domainsParam, true);
        } else {
            $domains = [];
        }
        $domains[$this->name] = $this;

        ContextManager::setParameterValue(self::NSPARAM, "DOMAIN_CONFIG", json_encode($domains));

        $dbDomain=new SearchDomainDatabase($this->name);
        $dbDomain->initialize();
    }


    public function reindexSearchDataElement(SmartElement $se)
    {
        $db=new SearchDomainDatabase($this->name);
        $db->updateSmartElement($se);
    }

    public function reindexSearchData()
    {
        $db=new SearchDomainDatabase($this->name);
        $db->initialize();
        $db->resetData(true);
    }
    public function updateIndexSearchData()
    {
        $db=new SearchDomainDatabase($this->name);
        $db->initialize();
        $db->resetData(false);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "lang" => $this->lang,
            "stem" => $this->stem,
            "configs" => $this->configs
        ];
    }
}
