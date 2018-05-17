<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\Pu;

use Anakeen\SmartHooks;

class TstInvisible extends \Anakeen\SmartStructures\Document
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTIMPORT, function (array $extra) {
            if (!empty($extra["state"])) {
                return $this->setState($extra["state"]);
            } else {
                $this->wid = 0;
                $this->state = '';
                $this->modify();
            }
            return '';
        });
    }
}

