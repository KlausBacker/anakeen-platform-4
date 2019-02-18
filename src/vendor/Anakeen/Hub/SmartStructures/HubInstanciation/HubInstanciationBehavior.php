<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation;

use Anakeen\Exception;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Hubinstanciation as HubinstanciationFields;

class HubInstanciationBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(
            SmartHooks::PRESTORE,
            function () {
                $this->getFavIcon();
                $this->affectLogicalName();
            }
        );
    }

    public function getCustomTitle()
    {
        $titles = $this->getArrayRawValues(HubinstanciationFields::hub_instance_titles);
        $finalTitle = $this->title;
        if ($titles) {
            $finalTitle = "";
            foreach ($titles as $title) {
                $finalTitle = $finalTitle . $title[HubinstanciationFields::hub_instance_title]."/";
            }
        }
        $finalTitle = preg_replace("/\/$/", '', $finalTitle);
        return $finalTitle;
    }

    public function getFavIcon()
    {
        $icon = $this->icon;
        $newIcon = $this->getRawValue(HubinstanciationFields::hub_instanciation_icone);
        if ($newIcon) {
            $icon = $newIcon;
            $this->icon = $newIcon;
            return $icon;
        }

        return $icon;
    }

    protected function affectLogicalName()
    {
        $instanceName= $this->getRawValue(HubinstanciationFields::instance_logical_name);
        if ($this->name !== $instanceName) {
            $err = $this->setLogicalName($instanceName, true);
            if ($err) {
                throw new Exception($err);
            }
        }
    }
}
