<?php


namespace Anakeen\SmartStructures\RenderDescription;

use Anakeen\SmartHooks;
use Anakeen\SmartStructures\Dir\DirHooks;

class RenderDescriptionHooks extends \Anakeen\SmartElement
{

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            //return $this->synchronizeSystemGroup();
        });
    }

    protected function completeFieldDescription()
    {
    }
}
