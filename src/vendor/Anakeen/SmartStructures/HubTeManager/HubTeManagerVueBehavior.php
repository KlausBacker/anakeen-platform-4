<?php

namespace Anakeen\SmartStructures\HubTeManager;

use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Hubconfigurationvue;

class HubTeManagerVueBehavior extends Hubconfigurationvue
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
            "name" => "ank-hub-te-manager",

            // Properties to use for the components
            "props" => []
        ];
    }

    /**
     * @return array
     * @throws \Anakeen\Ui\Exception
     */
    public static function getJSAsset()
    {
        $asset = UIGetAssetPath::getElementAssets("teManager", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        if (isset($asset["te-manager"]["js"])) {
            return [
                $asset["te-manager"]["js"]
            ];
        }
        $assets=parent::getAssets(\SmartStructure\Hubtemanagervue::familyName);
        return $assets["js"];
    }

    protected function getEntryOptions()
    {
        $entryOptions = parent::getEntryOptions();
        $entryOptions["libName"] = "AdminTeManager";
        return $entryOptions;
    }
}
