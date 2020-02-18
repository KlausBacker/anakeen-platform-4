<?php


namespace Anakeen\Fullsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Utils\Xml;
use Anakeen\Exception;

class ImportSearchConfiguration
{
    const NSURL = \Anakeen\Core\SmartStructure\ExportConfiguration::NSBASEURL . "search-domain/1.0";
    const ns = "sd";
    /**
     * @var \DOMDocument
     */
    protected $dom;
    /**
     * @var \DOMXPath
     */
    protected $xpath;
    protected $prefix;
    protected $domainName;
    protected $stem;
    protected $configs = [];
    /** @var SearchDomain */
    protected $domain;

    public function __construct($xmlFile)
    {
        $this->dom = new \DOMDocument();
        $this->dom->load($xmlFile);


        if (!Xml::getPrefix($this->dom, self::NSURL)) {
            throw new Exception("FSEA0001", $xmlFile);
        }

        $this->xpath = new \DOMXPath($this->dom);
        $this->xpath->registerNamespace(self::ns, self::NSURL);
    }

    public function import()
    {
        $this->recordConfig();
        $this->domain->reindexSearchData();
    }


    public function recordConfig()
    {
        $this->analyzeXml();
        $this->domain->record();
    }


    protected function analyzeXml()
    {
        $this->domain = new SearchDomain();
        $this->domain->name = $this->dom->documentElement->getAttribute("name");
        $this->domain->lang = substr($this->dom->documentElement->getAttribute("lang"), 0, 2);
        $this->domain->stem = $this->xpath->evaluate("string(sd:search-stem)");

        $locales = ContextManager::getLocales();
        foreach ($locales as $kLocale => $locale) {
            if ($locale["locale"] === $this->domain->lang) {
                $this->domain->lang = $kLocale;
                break;
            }
        }

        $configs = $this->xpath->query("sd:search-config");
        foreach ($configs as $config) {
            $this->domain->configs[] = $this->analyzeConfigXml($config);
        }
    }

    protected function analyzeConfigXml(\DOMElement $configNode)
    {
        $config = new SearchConfig();
        $config->structure = $configNode->getAttribute("structure");


        $fieldNodes = $this->xpath->query("sd:field", $configNode);
        foreach ($fieldNodes as $fieldNode) {
            /** @var \DOMElement $fieldNode */
            $config->fields[] = new SearchFieldConfig(
                $fieldNode->getAttribute("ref"),
                $fieldNode->getAttribute("weight")
            );
        }

        $fieldNodes = $this->xpath->query("sd:title", $configNode);
        foreach ($fieldNodes as $fieldNode) {
            /** @var \DOMElement $fieldNode */
            $config->fields[] = new SearchFieldConfig(
                "title",
                $fieldNode->getAttribute("weight")
            );
        }

        $fieldNodes = $this->xpath->query("sd:file", $configNode);
        foreach ($fieldNodes as $fieldNode) {
            /** @var \DOMElement $fieldNode */
            $config->fields[] = new SearchFileConfig(
                $fieldNode->getAttribute("ref"),
                $fieldNode->getAttribute("weight"),
                $fieldNode->getAttribute("filename") ?: false,
                $fieldNode->getAttribute("filecontent") ?: false,
                $fieldNode->getAttribute("filetype") ?: false
            );
        }

        return $config;
    }
}
