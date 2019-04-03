<?php

namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp;

use SmartStructure\Fields\DSearch as DSearchFields;
use Anakeen\SmartElementManager;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Hubconfigurationvue;
use SmartStructure\Fields\Hubbusinessapp as HubBusinessAppFields;

class HubBusinessAppBehavior extends Hubconfigurationvue
{

    public function registerHooks()
    {
        parent::registerHooks();
    }

    /**
     * Get component specific configuration, to display it correctly with its options
     *
     * @return array
     */
    protected function getComponentConfiguration()
    {
        return [
            // Name of the Vue.js component
            "name" => "ank-business-app",

            // Properties to use for the components
            "props" => [
                "collections" => $this->getCollections(),
                "welcomeTab" => $this->getWelcomeTabConfiguration()
            ]
        ];
    }

    /**
     * @return array
     * @throws \Anakeen\Ui\Exception
     */
    public static function getJSAsset()
    {
        $asset = UIGetAssetPath::getElementAssets("businessApp", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        if (isset($asset["business-app"]["js"])) {
            return [
                $asset["business-app"]["js"]
            ];
        }
        $assets = parent::getAssets(\SmartStructure\Hubadmincenteraccountsvue::familyName);
        return $assets["js"];
    }

    protected function getEntryOptions()
    {
        $entryOptions = parent::getEntryOptions();
        $entryOptions["libName"] = "HubBusinessApp";
        return $entryOptions;
    }

    protected function getCollections()
    {
        $collectionsInfo = [];
        $collectionsIds = $this->getAttributeValue(HubBusinessAppFields::hba_collection);
        foreach ($collectionsIds as $collectionId) {
            $smartEl = SmartElementManager::getDocument($collectionId);
            if (!empty($smartEl)) {
                $infos =  [
                    "title" => $smartEl->getTitle(),
                    "initid" => $smartEl->initid,
                    "id" => $smartEl->id,
                    "name" => $smartEl->name,
                    "icon" => $smartEl->getIcon("", 24)
                ];
                $structureRef = SmartElementManager::getDocument($smartEl->getRawValue("se_famid"));
                if (!empty($structureRef)) {
                    $infos["displayIcon"] = $structureRef->getIcon("", 24);
                } else {
                    $infos["displayIcon"] = $infos["icon"];
                }
                $collectionsInfo[] = $infos;
            }
        }
        return $collectionsInfo;
    }

    protected function getCreationConfig()
    {
        $creationConfigs = [];
        foreach ($this->getAttributeValue(HubBusinessAppFields::hba_structure) as $structureId) {
            if (!empty($structureId)) {
                $structure = SmartElementManager::getFamily($structureId);
                $creationConfigs[] = [
                    "title" => $structure->getTitle(),
                    "icon" => $structure->getIcon("", 24),
                    "name" => $structure->name,
                    "id" => $structure->id
                ];
            }
        }
        return $creationConfigs;
    }

    protected function getGridCollections()
    {
        $collectionsInfo = [];
        $collectionsIds = $this->getAttributeValue(HubBusinessAppFields::hba_grid_collection);
        foreach ($collectionsIds as $collectionId) {
            $smartEl = SmartElementManager::getDocument($collectionId);
            if (!empty($smartEl)) {
                $infos =  [
                    "title" => $smartEl->getTitle(),
                    "initid" => $smartEl->initid,
                    "id" => $smartEl->id,
                    "name" => $smartEl->name,
                    "icon" => $smartEl->getIcon("", 24)
                ];
                $structureRef = SmartElementManager::getDocument($smartEl->getRawValue("se_famid"));
                if (!empty($structureRef)) {
                    $infos["displayIcon"] = $structureRef->getIcon("", 24);
                } else {
                    $infos["displayIcon"] = $infos["icon"];
                }
                $collectionsInfo[] = $infos;
            }
        }
        return $collectionsInfo;
    }

    protected function getWelcomeTabConfiguration()
    {
        if ($this->getRawValue(HubBusinessAppFields::hba_welcome_option) === "NO") {
            return false;
        }
        return [
            "creation" => $this->getCreationConfig(),
            "gridCollections" => $this->getGridCollections()
        ];
    }
}
