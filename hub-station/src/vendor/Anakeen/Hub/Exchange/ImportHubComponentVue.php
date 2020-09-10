<?php


namespace Anakeen\Hub\Exchange;

use SmartStructure\Fields\Hubconfigurationvue as VueFields;

class ImportHubComponentVue extends ImportHubComponent
{
    protected function getCustomMapping()
    {
        $customMapping=parent::getCustomMapping();

        $mapping = [
            "router-entry" => VueFields::hub_vue_router_entry,



        ];

        return array_merge($customMapping, $mapping);
    }
}
