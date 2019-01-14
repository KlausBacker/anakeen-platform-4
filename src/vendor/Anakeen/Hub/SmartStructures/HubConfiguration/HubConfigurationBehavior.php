<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration;

use Anakeen\Core\ContextManager;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getConfiguration()
    {
        // Config to return
        $configuration = [];

        $configuration["tab"] = [];
        $configuration["tab"]["expanded"] = "<span>".$this->getHubConfigurationTitle()."</span>";
        $configuration["position"] = $this->getAttributeValue(HubConfigurationFields::hub_order);
        //$DockerPosition = $this->getAttributeValue(HubConfigurationSlotFields::hub_docker_position);

        // Default configuration : Elements are in the body, and selectable
        $configuration["area"] = "body";
        $configuration["tab"]["selectable"] = true;
        $configuration["tab"]["selected"] = false;

        // Component is in the content of the dock element, and compact is the selected icon
        $configuration["tab"]["compact"] = $this->getHubConfigurationIcon();
        $configuration["tab"]["content"] = $this->getComponentConfiguration();

        return $configuration;
    }

    /**
     * Get Hub configuration title corresponding to the user language
     *
     * @return string
     */
    protected function getHubConfigurationTitle()
    {
        $titles = $this->getArrayRawValues("hub_titles");
        $language = ContextManager::getLanguage();
        $defaultTitle = "";

        foreach ($titles as $title) {
            if ($title["hub_language_code"] == "en-US") {
                $defaultTitle = $title["hub_title"];
            }
            if (strpos(str_replace("_", "-", $language), $title["hub_language_code"]) === 0) {
                return $title["hub_title"];
            }
        }

        return $defaultTitle;
    }

    /**
     * Get Hub configuration icon
     *
     * @return string
     */
    protected function getHubConfigurationIcon()
    {
        $iconEnum = $this->getRawValue("hub_icon_enum");
        $finalIcon = $this->icon;
        switch($iconEnum) {
            case "IMAGE":
                $finalIcon = $this->getRawValue("hub_icon_image");
                break;
            case "HTML":
                $finalIcon = $this->getRawValue("hub_icon_text");
                break;
            case "FONT":
                $finalIcon = $this->getRawValue("hub_icon_font");
                break;
            default:
                break;
        }
        return $finalIcon;
    }

    /**
     * Get component configuration
     *
     * @return array
     */
    protected function getComponentConfiguration()
    {
        return [
            "componentName" => "",
            "props" => []
        ];
    }

    public function getCustomTitle()
    {
        $titles = $this->getArrayRawValues("hub_titles");
        $finalTitle = $this->title;
        if ($titles) {
            $finalTitle = "";
            foreach ($titles as $title) {
                $finalTitle = $finalTitle . $title["hub_title"]."/";
            }
        }
        $finalTitle = preg_replace("/\/$/", '', $finalTitle );
        return $finalTitle;
    }
}
