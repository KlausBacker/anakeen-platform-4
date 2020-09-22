<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\AccountManager;
use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ImportSmartConfiguration;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Core\Utils\Postgres;
use Anakeen\Core\Utils\Xml;
use Anakeen\Core\VaultManager;
use SmartStructure\Fields\Hubinstanciation as HubFields;

class ImportHubConfiguration extends ImportSmartConfiguration
{

    protected $smartNs = HubExport::NSHUBURL;
    protected $defaultNsPrefix = "hub";


    protected function importConfigurations()
    {
        $this->smartPrefix = Xml::getPrefix($this->dom, $this->smartNs);
        $configs = $this->getNodes($this->dom->documentElement, "instance");

        $data = [];
        foreach ($configs as $config) {
            $data = array_merge($data, $this->importHubInstance($config));
        }

        $componentsParameter = $this->dom->documentElement->getElementsByTagNameNS(
            \Anakeen\Hub\Exchange\HubExportComponent::$NSHUBURLCOMPONENT,
            "parameters"
        );


        $hubComponentImport = new ImportHubComponent();
        /** @var \DOMNode $componentParameter */
        foreach ($componentsParameter as $componentParameter) {
            $component = $componentParameter->parentNode;
            /** @var \DOMElement $component */
            $data = array_merge($data, $hubComponentImport->importHubComponent($component));
        }


        $this->recordSmartData($data);
        return $data;
    }

    protected function importHubInstance(\DOMElement $config)
    {
        $hubInstance = SEManager::getFamily(\SmartStructure\Hubinstanciation::familyName);
        $mapping = [
            "@name" => HubFields::instance_logical_name,
            "description/icon" => HubFields::hub_instanciation_icone,
            "description/title" => HubFields::hub_instance_title,
            "description/title/@lang" => [
                HubFields::hub_instance_language,
                function ($v) {
                    switch (substr($v, 0, 2)) {
                        case "fr":
                            return "Français";
                        case "en":
                            return "English";
                    }
                    return ucfirst($v);
                }
            ],
            "settings/css" => [
                HubFields::hub_instance_cssasset,
                function ($v, $nodeCss) {
                    return $this->getAssetConfig($nodeCss);
                }
            ],
            "settings/css/@type" => HubFields::hub_instance_cssasset_type,
            "settings/js" => [
                HubFields::hub_instance_jsasset,
                function ($v, $nodeJs) {
                    return $this->getAssetConfig($nodeJs);
                }
            ],
            "settings/js/@type" => HubFields::hub_instance_jsasset_type,
            "settings/router-entry" => HubFields::hub_instanciation_router_entry,
            "settings/docks/dock-left/collapse" => HubFields::hub_instanciation_dock_left,
            "settings/docks/dock-right/collapse" => HubFields::hub_instanciation_dock_right,
            "settings/docks/dock-top/collapse" => HubFields::hub_instanciation_dock_top,
            "settings/docks/dock-bottom/collapse" => HubFields::hub_instanciation_dock_bottom,
            "security/access-roles/access-role/@login" => HubFields::hub_access_roles,
            "security/super-role/@login" => HubFields::hub_super_role,
        ];

        return $this->applyMapping($config, $mapping, $hubInstance);
    }


    protected function getXPath($prefix)
    {
        $xpath = new \DOMXpath($this->dom);
        $xpath->registerNamespace(
            $prefix,
            $this->smartNs
        );

        return $xpath;
    }

    protected function evaluateNs(string $path, \DOMElement $domElement)
    {
        $prefix = "hubx";
        $xpath = $this->getXPath($prefix);
        $parts = explode("/", $path);
        $nsParts = [];
        foreach ($parts as $part) {
            if ($part[0] !== "@") {
                $nsParts[] = $this->defaultNsPrefix . ":" . $part;
            } else {
                $nsParts[] = $part;
            }
        }
        $nsPath = implode("/", $nsParts);
        return $xpath->evaluate(sprintf("string(%s)", $nsPath), $domElement);
    }

    protected function applyMapping(\DOMElement $config, array $mapping, SmartStructure $structure)
    {
        $prefix = "hubx";
        $xpath = $this->getXPath($prefix);

        $name = $xpath->evaluate("string(@name)", $config);
        $orders = ["ORDER", $structure->name, "", ""];
        $data = ["DOC", $structure->name, $name, ""];
        foreach ($mapping as $path => $fieldId) {
            $parts = explode("/", $path);
            $nsParts = [];
            foreach ($parts as $part) {
                if ($part[0] !== "@") {
                    $nsParts[] = $this->defaultNsPrefix . ":" . $part;
                } else {
                    $nsParts[] = $part;
                }
            }
            $nsPath = implode("/", $nsParts);
            $fieldClosure = null;
            if (is_array($fieldId)) {
                list($fieldId, $fieldClosure) = $fieldId;
            }

            $oa = $structure->getAttribute($fieldId);
            $value = "";
            if ($oa && $oa->isMultiple()) {
                $values = $xpath->query($nsPath, $config);
                $stringValues = [];
                /** @var \DOMNode $nodeValue */
                foreach ($values as $nodeValue) {
                    $stringValue = $this->xml2smartValue(
                        $oa,
                        $nodeValue->nodeValue === "" ? null : $nodeValue->nodeValue
                    );
                    if ($fieldClosure) {
                        $stringValue = $fieldClosure($stringValue, $nodeValue);
                    }
                    $stringValues[] = $stringValue;
                }
                if ($stringValues) {
                    $value = Postgres::arrayToString($stringValues);
                }
            } else {
                $value = $this->xml2smartValue($oa, $xpath->evaluate(sprintf("string(%s)", $nsPath), $config));
                if ($fieldClosure) {
                    $nodeValue = $xpath->query($nsPath, $config);
                    $value = $fieldClosure($value, $nodeValue);
                }
            }

            if ($value) {
                if ($oa && ($oa->type === "image" || $oa->type === "file")) {
                    $fileTitle = $xpath->evaluate(sprintf("string(%s/@title)", $nsPath), $config);
                    $filePath = sprintf("%s/%s%s", ContextManager::getTmpDir(), uniqid("hub"), $fileTitle);
                    file_put_contents($filePath, base64_decode($value));
                    $vaultId = VaultManager::storeFile($filePath, $fileTitle);
                    $info = VaultManager::getFileInfo($vaultId);
                    $value = sprintf("%s|%s|%s", $info->mime_s, $info->id_file, $info->name);
                }
            }
            $orders[] = $fieldId;
            $data[] = $value;
        }
        return [$orders, $data];
    }

    protected function getAssetConfig(\DOMElement $nodeJs)
    {
        $type = $nodeJs->getAttribute("type");

        if ($type === "manifest") {
            $ft = $this->evaluateNs("asset-callable/@function", $nodeJs);
            $argsNode = $this->getNodes($nodeJs, "asset-argument");
            $args = [];
            foreach ($argsNode as $argNode) {
                /** @var \DOMElement $argNode */
                $args[] = sprintf('"%s"', $argNode->nodeValue);
            }
            return sprintf("%s(%s)", $ft, implode(",", $args));
        }
        // type path
        return $nodeJs->nodeValue;
    }

    protected function xml2smartValue($oa, $value)
    {
        if ($value) {
            if ($oa && ($oa->type === "account")) {
                $u = AccountManager::getAccount($value);
                if ($u) {
                    $value = $u->fid;
                }
            }
        }
        return $value;
    }
}
