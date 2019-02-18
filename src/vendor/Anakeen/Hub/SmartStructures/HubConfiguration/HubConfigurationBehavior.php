<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration;

use Anakeen\Core\SEManager;
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

        $configuration["assets"] = $this->getAssets();
        $configuration["position"] = $this->getPositionConfiguration();
        $configuration["component"] = $this->getComponentConfiguration();

        $configuration["entryOptions"] = $this->getEntryOptions();
        return $configuration;
    }

    protected function getPositionConfiguration() {
        $dockPosition = static::getDockPosition($this->getAttributeValue(HubConfigurationFields::hub_docker_position));
       return [
            "order" => $this->getAttributeValue(HubConfigurationFields::hub_order),
            "dock" => $dockPosition["dock"],
            "innerPosition" => $dockPosition["innerPosition"]
        ];
    }

    protected function getEntryOptions() {
        return  [
            "selected" => $this->getAttributeValue(HubConfigurationFields::hub_activated) === "TRUE",
            "selectable" => $this->getAttributeValue(HubConfigurationFields::hub_selectable) === "TRUE"
        ];
    }


    protected function getAssets()
    {
        $assets = [];
        $assets["js"] = SEManager::getFamily($this->fromname)->getFamilyParameterValue("hub_jsasset", []);
        $assets["css"] = SEManager::getFamily($this->fromname)->getFamilyParameterValue("hub_cssasset", []);
        return $assets;
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
                "msg" => "???"
            ]
        ];
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
