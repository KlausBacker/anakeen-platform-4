<?php

namespace Anakeen\SmartStructures\Devclient;

use Anakeen\SmartHooks;
use SmartStructure\Fields\Devclient as DevclientFields;

class DevclientBehavior extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(
            SmartHooks::PRESTORE,
            function () {
                $this->setValue(
                    DevclientFields::client_title,
                    sprintf(
                        "%s %s",
                        mb_strtoupper($this->getRawValue(DevclientFields::client_lastname)),
                        $this->getRawValue(DevclientFields::client_firstname)
                    )
                );
            }
        );
    }
}
