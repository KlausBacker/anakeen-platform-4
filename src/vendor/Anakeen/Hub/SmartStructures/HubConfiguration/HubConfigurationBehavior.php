<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(
            SmartHooks::PRESTORE,
            function () {
                $this->getHubConfigurationIcon();
            }
        );
    }

    public function getConfiguration()
    {
        // Config to return
        $configuration = [];

        $configuration["dock"] = $this->getAttributeValue(HubConfigurationFields::hub_docker_position);
        $configuration["assets"] = $this->getAssets();
        $configuration["tab"] = [];
        $configuration["tab"]["expanded"] = "<span>".$this->getHubConfigurationTitle()."</span>";
        $configuration["position"] = $this->getAttributeValue(HubConfigurationFields::hub_order);
        //$DockerPosition = $this->getAttributeValue(HubConfigurationSlotFields::hub_docker_position);

        // Default configuration : Elements are in the body, and selectable
        $configuration["area"] = $this->getHubPosition($this->getAttributeValue(HubConfigurationFields::hub_docker_position));
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

    protected function getAssets()
    {
        $assets = [];
        $assets["js"] = SEManager::getFamily($this->fromname)->getFamilyParameterValue("hub_jsasset", []);
        $assets["css"] = SEManager::getFamily($this->fromname)->getFamilyParameterValue("hub_cssasset", []);
        return $assets;
    }

    /**
     * Get Hub configuration icon
     *
     * @return string
     */
    protected function getHubConfigurationIcon()
    {
        $iconEnum = $this->getRawValue("hub_icon_enum");
        switch ($iconEnum) {
            case "IMAGE":
                $this->setValue("hub_final_icon", "<img src='".$this->getRawValue("hub_icon_image")."'>");
                break;
            case "HTML":
                $this->setValue("hub_final_icon", "<i class='fa fa-".$this->getRawValue("hub_icon_text")."'></i>");
                break;
            case "FONT":
                $this->setValue("hub_final_icon", "<i class='fa fa-".$this->getRawValue("hub_icon_font")."'></i>");
                break;
            default:
                break;
        }
        return $this->getRawValue("hub_final_icon");
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
        $finalTitle = preg_replace("/\/$/", '', $finalTitle);
        return $finalTitle;
    }

    protected function getHubPosition($position)
    {
        switch ($position) {
            case "LEFT_TOP":
            case "RIGHT_TOP":
            case "TOP_LEFT":
            case "BOTTOM_LEFT":
                return "header";
            case "LEFT_CENTER":
            case "RIGHT_CENTER":
            case "BOTTOM_CENTER":
            case "TOP_CENTER":
                return "body";
            case "LEFT_BOTTOM":
            case "RIGHT_BOTTOM":
            case "BOTTOM_RIGHT":
            case "TOP_RIGHT":
                return "footer";
            default:
                break;
        }
    }
}
