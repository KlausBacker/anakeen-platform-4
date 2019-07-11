<?php

namespace Anakeen\SmartStructures\Devperson;

use Anakeen\SmartHooks;
use SmartStructure\Fields\Devperson as DevpersonFields;

class DevpersonBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(
            SmartHooks::PRESTORE, function () {
                $this->setValue(
                    DevPersonFields::dev_title, sprintf(
                        "%s %s",
                        mb_strtoupper($this->getRawValue(DevPersonFields::dev_lastname)),
                        $this->getRawValue(DevPersonFields::dev_firstname)
                    )
                );
            }
        );
    }
}
