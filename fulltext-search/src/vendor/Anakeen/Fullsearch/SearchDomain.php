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
        $domains=SearchDomainManager::getConfig();
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
        SearchDomainManager::recordDomainConfig($this);
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
        $db->recordData(true);
    }
    public function updateIndexSearchData(\Closure $onUpdate = null)
    {
        $db=new SearchDomainDatabase($this->name);
        $db->initialize();
        if ($onUpdate) {
            $db->onUpdate($onUpdate);
        }
        $db->recordData(false);
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
