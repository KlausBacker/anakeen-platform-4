<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationVue;

use Anakeen\Core\SEManager;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Hubconfigurationvue as HubConfigurationVueFields;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

class HubConfigurationVueBehavior extends \SmartStructure\Hubconfiguration
{
    protected function getEntryOptions()
    {
        $entryOptions = parent::getEntryOptions();
        $entryOptions["route"] = $this->getRawValue(
            HubConfigurationVueFields::hub_vue_router_entry,
            $this->getRawValue(HubConfigurationVueFields::hub_title)
        );
        $hubInstanceParent = $this->getRawValue(HubConfigurationVueFields::hub_station_id, null);
        if (!empty($hubInstanceParent)) {
            $instance = SEManager::getDocument($hubInstanceParent);
            if (!empty($instance)) {
                $configuration = $instance->getConfiguration();
                $entryOptions["completeRoute"] = preg_replace("/\/\/+/", "/", ($configuration["routerEntry"] . "/" . $entryOptions["route"]));
            }
        }
        return $entryOptions;
    }

    public static function getRouterEntryPattern($routerEntry)
    {
        $result = $routerEntry;
        if ($result[0] === "/") {
            $result = substr($result, 1);
        }
        if ($result[strlen($result) - 1] === "/") {
            $result = substr($result, 0, strlen($result) - 1);
        }
        return "/*" . preg_replace("|/+|", "/+", $result) . "/*";
    }

    public function checkRouterEntry($routerEntry, $hubInstanceId)
    {
        if (!empty($routerEntry)) {
            if (!preg_match("/^[A-Za-z]+[A-Za-z\d]*/", $routerEntry)) {
                return ___(
                    sprintf("the router entry '%s' has not a valid url format", $routerEntry),
                    "HubConfigurationVueBehavior"
                );
            }
        }

        if (!empty($routerEntry)) {
            $fixtureUrlBase = "http://www.example-anakeen.com";
            if ($routerEntry[0] !== "/") {
                $fixtureUrlBase .= "/";
            }
            if (!filter_var($fixtureUrlBase . $routerEntry, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                return ___(
                    sprintf("the router entry '%s' has not a valid url format", $routerEntry),
                    "HubConfigurationVueBehavior"
                );
            }
        }
        if (!empty($hubInstanceId) && !empty($routerEntry)) {
            $search = new SearchElements($this->fromname);
            $search->addFilter("%s = '%d'", HubConfigurationVueFields::hub_station_id, $hubInstanceId);
            $search->addFilter("id <> '%d'", $this->initid);
            $search->addFilter(
                "%s SIMILAR TO '%s'",
                HubConfigurationVueFields::hub_vue_router_entry,
                static::getRouterEntryPattern($routerEntry)
            );
            $results = $search->getResults();
            $titles = [];
            foreach ($results as $result) {
                $titles[] = $result->getTitle();
            }
            if (count($results) > 0) {
                return ___(sprintf(
                    "the router entry '%s' is already matched by '%s'",
                    $routerEntry,
                    implode(",", $titles)
                ), "HubConfigurationVueBehavior");
            }
        }
        return "";
    }
}
