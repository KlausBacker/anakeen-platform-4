<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration;

use Anakeen\Core\AccountManager;
use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\Search\SearchElements;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

class HubConfigurationBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::PRESTORE, function () {
            // Copy parameters to simplify render and searches
            $this->setValue(HubConfigurationFields::hub_execution_roles, $this->getExecutionRoles());
        });
    }

    public function getConfiguration()
    {
        // Config to return
        $configuration = [];

        $configuration["assets"] = $this->getAssets($this->fromname);
        $configuration["position"] = $this->getPositionConfiguration();
        $configuration["component"] = $this->getComponentConfiguration();

        $configuration["entryOptions"] = $this->getEntryOptions();
        return $configuration;
    }

    protected function getExecutionRoles()
    {
        $routeRoleRef = explode(",", $this->getFamilyParameterValue(HubConfigurationFields::hub_p_routes_role));
        $roles = [];
        foreach ($routeRoleRef as $roleLogin) {
            $roleLogin = trim($roleLogin);
            if ($roleLogin) {
                $u = AccountManager::getAccount($roleLogin);
                if (!$u || !$u->fid) {
                    throw new Exception(sprintf("Role %s not exists", $roleLogin));
                }
                $roles[] = $u->fid;
            }
        }
        return $roles;
    }

    protected function getPositionConfiguration()
    {
        $dockPosition = static::getDockPosition($this->getAttributeValue(HubConfigurationFields::hub_docker_position));
        return [
            "order" => $this->getAttributeValue(HubConfigurationFields::hub_order),
            "dock" => $dockPosition["dock"],
            "innerPosition" => $dockPosition["innerPosition"]
        ];
    }

    protected function getEntryOptions()
    {
        return [
            "activated" => $this->getRawValue(HubConfigurationFields::hub_activated) === "TRUE",
            "activatedOrder" => $this->getAttributeValue(HubConfigurationFields::hub_activated_order),
            "selectable" => $this->getRawValue(HubConfigurationFields::hub_selectable) === "TRUE",
            "expandable" => $this->getRawValue(HubConfigurationFields::hub_expandable) === "TRUE"
        ];
    }


    protected static function getAssets($structureName)
    {
        $assets = [];
        $assets["js"] = SEManager::getFamily($structureName)->getFamilyParameterValue("hub_jsasset", []);
        $assets["css"] = SEManager::getFamily($structureName)->getFamilyParameterValue("hub_cssasset", []);
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
        $position = ["dock" => "", "innerPosition" => ""];
        if (!empty($dockPosition)) {
            $tokens = explode("_", $dockPosition);
            if (!empty($tokens) && count($tokens) > 0) {
                $position["dock"] = $tokens[0];
                $position["innerPosition"] = static::getInnerPosition($tokens[1]);
            }
        }
        return $position;
    }

    public function checkHubElementName($hubElementName, $hubInstanceId)
    {
        if (!empty($hubInstanceId)) {
            $search = new SearchElements($this->fromname);
            $search->addFilter("%s = '%d'", HubConfigurationFields::hub_station_id, $hubInstanceId);
            $search->addFilter("id <> '%d'", $this->initid);
            $search->addFilter("%s SIMILAR TO '%s'", HubConfigurationFields::hub_title, $hubElementName);
            $results = $search->getResults();
            $titles = [];
            foreach ($results as $result) {
                $titles[] = $result->getTitle();
            }
            if (count($results) > 0) {
                return ___(sprintf("the name '%s' is already affected to '%s'", $hubElementName, implode(",", $titles)), "HubConfigurationVueBehavior");
            }
        }
        return "";
    }
}
