<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation;

use Anakeen\SmartHooks;

class HubInstanciationBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(
            SmartHooks::PRESTORE,
            function () {
                $this->getFavIcon();
            }
        );
    }

    public function getCustomTitle()
    {
        $titles = $this->getArrayRawValues("hub_instance_titles");
        $finalTitle = $this->title;
        if ($titles) {
            $finalTitle = "";
            foreach ($titles as $title) {
                $finalTitle = $finalTitle . $title["hub_instance_title"]."/";
            }
        }
        $finalTitle = preg_replace("/\/$/", '', $finalTitle);
        return $finalTitle;
    }

    public function getFavIcon()
    {
        $icon = $this->icon;
        $newIcon = $this->getRawValue("hub_instanciation_icone");
        if ($newIcon) {
            $icon = $newIcon;
            return $icon;
        }

        return $icon;
    }
}
