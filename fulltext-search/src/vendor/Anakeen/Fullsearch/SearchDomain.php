<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Exception;

class SearchDomain implements \JsonSerializable
{
    const NSPARAM = "Fullsearch";
    public $name;
    public $stem;
    public $description;
    /** @var SearchConfig[] */
    public $configs = [];
    /**
     * @var string
     */
    public $lang;

    /**
     * SearchDomain constructor.
     * @param string $name Search Domain Identifier
     * @throws Exception
     */
    public function __construct(string $name = "")
    {
        if ($name) {
            $this->affect($name);
        }
    }

    /**
     * Instance object from parameter config
     * @param string $name Search Domain Identifier
     * @throws Exception
     */
    public function affect($name)
    {
        $domains = SearchDomainManager::getConfig();
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


    /**
     * Record config and create database tables
     * @throws Exception
     */
    public function record()
    {
        SearchDomainManager::recordDomainConfig($this);
        $dbDomain = new SearchDomainDatabase($this->name);
        $dbDomain->initialize();
    }


    /**
     * Reset indexing for a Smart Element for current domain
     * @param SmartElement $se
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public function reindexSearchDataElement(SmartElement $se)
    {
        $db = new SearchDomainDatabase($this->name);
        $db->updateSmartElement($se);
    }

    /**
     * Reset all search data that are not up-to-date
     * Delete all previously recorded searching data
     * @param \Closure|null $onUpdate callback call before update
     * @throws \Anakeen\Exception
     */
    public function reindexSearchData(\Closure $onUpdate = null)
    {
        $db = new SearchDomainDatabase($this->name);
        $db->initialize();
        if ($onUpdate) {
            $db->onUpdate($onUpdate);
        }
        $db->recordData(true);
    }

    /**
     * Update all search data that are not up-to-date
     * @param \Closure|null $onUpdate callback call before update
     * @throws \Anakeen\Exception
     */
    public function updateIndexSearchData(\Closure $onUpdate = null)
    {
        $db = new SearchDomainDatabase($this->name);
        $db->initialize();
        if ($onUpdate) {
            $db->onUpdate($onUpdate);
        }
        $db->recordData(false);
    }

    public function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "lang" => $this->lang,
            "stem" => $this->stem,
            "description" => $this->description,
            "configs" => $this->configs
        ];
    }
}
