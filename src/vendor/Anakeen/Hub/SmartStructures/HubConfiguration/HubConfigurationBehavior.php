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

        $configuration["assets"] = $this->getAssets();
        $dockPosition = static::getDockPosition($this->getAttributeValue(HubConfigurationFields::hub_docker_position));
        $configuration["position"] = [
            "order" => $this->getAttributeValue(HubConfigurationFields::hub_order),
            "dock" => $dockPosition["dock"],
            "innerPosition" => $dockPosition["innerPosition"]
        ];
        $configuration["component"] = $this->getComponentConfiguration();

        $configuration["entryOptions"] = [
            "iconTemplate" => $this->getAttributeValue(HubConfigurationFields::hub_final_icon),
            "selected" => $this->getAttributeValue(HubConfigurationFields::hub_activated) === "TRUE",
            "selectable" => $this->getAttributeValue(HubConfigurationFields::hub_selectable) === "TRUE"
        ];
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
                $this->setValue("hub_final_icon", $this->getRawValue("hub_icon_text"));
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
            "name" => "",
            "props" => [
                "msg" => getAttributeValue()
            ]
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

    protected static function getInnerPosition($innerPosition)
    {
        switch ($innerPosition) {
            case "TOP":
            case "LEFT":
                return "HEADER";
            case "RIGHT":
            case "BOTTOM":
                return "FOOTER";
            default:
                return $innerPosition;
        }
    }

    protected static function getDockPosition($dockPosition)
    {
        $position = [ "dock" => "", "innerPosition" => ""];
        if (!empty($dockPosition)) {
            $tokens = explode("_", $dockPosition);
            if (!empty($tokens) && count($tokens) > 0) {
                $position["dock"] = $tokens[0];
                $position["innerPosition"] = static::getInnerPosition($tokens[1]);
            }
        }
        return $position;
    }
}
