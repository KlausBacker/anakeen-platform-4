<?php

namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Hubconfigurationvue;
use SmartStructure\Fields\Hubbusinessapp as HubBusinessAppFields;

class HubBusinessAppBehavior extends Hubconfigurationvue
{

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::PRESTORE, function () {
            $this->setIconValue();
        });
        $this->getHooks()->addListener(\Anakeen\Hub\Routes\ExportConfiguration::PREIMPORT, function (&$dataIds) {
            $hbaCollections = array_merge(
                $this->getAttributeValue(HubBusinessAppFields::hba_collection),
                $this->getAttributeValue(HubBusinessAppFields::hba_grid_collection)
            );
            foreach ($hbaCollections as $hbaCollection) {
                $dataIds[]=$hbaCollection;
            }
        });
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
                "welcomeTab" => $this->getWelcomeTabConfiguration(),
                "iconTemplate" => $this->getRawValue(HubBusinessAppFields::hba_icon),
                "hubLabel" => $this->getCustomTitle()
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
            $smartEl = SEManager::getDocument($collectionId);
            if (!empty($smartEl) && $smartEl->hasPermission("execute")) {
                $infos =  [
                    "title" => $smartEl->getTitle(),
                    "initid" => $smartEl->initid,
                    "id" => $smartEl->id,
                    "name" => $smartEl->name,
                    "icon" => $smartEl->getIcon("", 24)
                ];
                $structureRef = SEManager::getDocument($smartEl->getRawValue("se_famid"));
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
                $structure = SEManager::getFamily($structureId);
                if (!empty($structure) && $structure->hasPermission("create")) {
                    $creationConfigs[] = [
                        "title" => $structure->getTitle(),
                        "icon" => $structure->getIcon("", 24),
                        "name" => $structure->name,
                        "id" => $structure->id
                    ];
                }
            }
        }
        return $creationConfigs;
    }

    protected function getGridCollections()
    {
        $collectionsInfo = [];
        $collectionsIds = $this->getAttributeValue(HubBusinessAppFields::hba_grid_collection);
        foreach ($collectionsIds as $collectionId) {
            $smartEl = SEManager::getDocument($collectionId);
            if (!empty($smartEl) && $smartEl->hasPermission("execute")) {
                $infos =  [
                    "title" => $smartEl->getTitle(),
                    "initid" => $smartEl->initid,
                    "id" => $smartEl->id,
                    "name" => $smartEl->name,
                    "icon" => $smartEl->getIcon("", 24)
                ];
                $structureRef = SEManager::getDocument($smartEl->getRawValue("se_famid"));
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
            "gridCollections" => $this->getGridCollections(),
            "title" => $this->getRawValue(HubBusinessAppFields::hba_welcome_title, "Welcome")
        ];
    }

    public function getDefaultLanguages()
    {
        return [
            [
                HubBusinessAppFields::hba_title => "",
                HubBusinessAppFields::hba_language => "en_US"
            ],
            [
                HubBusinessAppFields::hba_title => "",
                HubBusinessAppFields::hba_language => "fr_FR"
            ]
        ];
    }

    protected function setIconValue()
    {
        $iconType = $this->getRawValue(HubBusinessAppFields::hba_icon_type);
        switch ($iconType) {
            case "ICON":
                $icon = $this->getRawValue(HubBusinessAppFields::hba_icon_lib);
                if (!empty($icon)) {
                    $this->setValue(HubBusinessAppFields::hba_icon, $icon);
                }
                break;
            case "HTML":
                $icon = $this->getRawValue(HubBusinessAppFields::hba_icon_html);
                if (!empty($icon)) {
                    $this->setValue(HubBusinessAppFields::hba_icon, htmlspecialchars_decode($icon));
                }
                break;
            case "IMAGE":
                $image = $this->getAttributeValue(HubBusinessAppFields::hba_icon_image);
                if (!empty($image)) {
                    $fileInfo = $this->getFileInfo($image);
                    $imgValue = base64_encode(file_get_contents($fileInfo["path"]));
                    $src = 'data: '.$fileInfo["mime_s"].';base64,'.$imgValue;
                    $this->setValue(
                        HubBusinessAppFields::hba_icon,
                        "<img src='" .$src. "' width='32' height='32'/>"
                    );
                }
                break;
        }
    }

    public function getCustomTitle()
    {
        $result = parent::getCustomTitle();
        // Creation mode perhaps ?
        if ($this->id === "") {
            return $result;
        }
        $titles = $this->getArrayRawValues(HubBusinessAppFields::hba_titles);
        $lang = explode(".", ContextManager::getLanguage());
        $currentLanguage = "fr_FR";
        if (!empty($lang) && count($lang) > 0) {
            $currentLanguage = $lang[0];
        }
        if ($titles) {
            foreach ($titles as $title) {
                if ($title[HubBusinessAppFields::hba_language] === $currentLanguage) {
                    $result = $title[HubBusinessAppFields::hba_title];
                }
            }
        }
        return $result;
    }
}
