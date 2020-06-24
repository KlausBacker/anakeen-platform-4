<?php

namespace Anakeen\SmartCriteria;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Utils\Glob;
use Anakeen\Router\Config\AccessInfo;
use \Anakeen\Core\Exception;
use \Anakeen\Router\Config\RouterInfo;

class SmartCriteriaConfigLib
{

    /**
     * @var SmartCriteriaConfig
     */
    protected static $config = null;

    /**
     * Extract configuration from config files included in "config" directory
     *
     * @return SmartCriteriaConfig
     * @throws Exception
     */
    const NS = "sc";
    const NSURL="https://platform.anakeen.com/4/schemas/sc/1.0";
    protected static $index=0;


    public static function getSmartCriteriaConfig()
    {
        if (self::$config) {
            return self::$config;
        }


        $paths = SmartCriteriaConfigManager::getSmartCriteriaConfigPaths();

        $configFiles = [];
        foreach ($paths as $configDir) {
            $configFiles = array_merge($configFiles, self::getConfigFiles($configDir));
        }
        if (is_array($configFiles)) {
            $config = [];
            foreach ($configFiles as $configFile) {
                $conf = self::xmlDecode($configFile);
                if ($conf === null) {
                    throw new Exception("CORE0019", $configFile);
                }
                $config = array_merge($config, $conf);
            }

            $config = json_decode(json_encode((array)$config, true));
            self::$config = new SmartCriteriaConfig($config);
            return self::$config;
        } else {
            throw new Exception("CORE0020", "no config files");
        }
    }

    protected static function getConfigFiles($dir)
    {
        return Glob::glob("$dir/**/*xml");
    }

    protected static function xmlDecode($configFile)
    {
        $xmlData = file_get_contents($configFile);

        $sxe = new \SimpleXMLElement($xmlData);
        $namespaces = $sxe->getNamespaces();
        $ns=array_search(self::NSURL, $namespaces);

        if (!$ns) {
            $ns=self::NS;
        }

        $simpleData = simplexml_load_string($xmlData, \SimpleXMLElement::class, 0, $ns, true);

        if ($simpleData === false) {
            throw new \Anakeen\SmartCriteria\Exception("SMARTCRITERIA0101", $configFile);
        }

        return self::normalizeSimpleData($simpleData);
    }

    protected static function normalizeSimpleData(\SimpleXMLElement $simpleData)
    {
        $data = [];
        foreach ($simpleData->filters as $filterArray) {
            foreach ($filterArray->filter as $filter) {
                $data[] = $filter;
            }
        }
        return $data;
    }
}
