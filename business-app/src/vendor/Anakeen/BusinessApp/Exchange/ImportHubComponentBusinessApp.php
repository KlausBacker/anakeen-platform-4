<?php


namespace Anakeen\BusinessApp\Exchange;

use Anakeen\Hub\Exchange\ImportHubComponentVue;
use SmartStructure\Fields\Hubbusinessapp as BaFields;

class ImportHubComponentBusinessApp extends ImportHubComponentVue
{
    public function getCustomMapping()
    {
        $this->smartNs = HubExportBusinessAppComponent::$nsUrl;
        $this->defaultNsPrefix = "hubba";

        $customMapping=parent::getCustomMapping();
        $mapping = [
            "welcome/@activated" => [
                BaFields::hba_welcome_option,
                function ($v) {
                    return $v === "true" ? "YES" : "NO";
                }
            ],
            "icon" => BaFields::hba_icon_image,
            "title" => BaFields::hba_title,
            "title/@lang" => [
                BaFields::hba_language,
                function ($v) {
                    switch (substr($v, 0, 2)) {
                        case "fr":
                            return "fr_FR";
                        case "en":
                            return "en_US";
                    }
                    return $v;
                }
            ],
            "collections/collection/@ref" => BaFields::hba_collection,
            "welcome/grids/collection/@ref" => BaFields::hba_grid_collection,
            "welcome/structures-creation/structure/@ref" => BaFields::hba_structure,
            "welcome/title" => BaFields::hba_welcome_title,
        ];

        return array_merge($customMapping, $mapping);
    }

    protected function getXPath($prefix)
    {
        $xpath = parent::getXPath($prefix);
        $xpath->registerNamespace(
            "hubba",
            HubExportBusinessAppComponent::$nsUrl
        );

        return $xpath;
    }
}
