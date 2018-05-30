<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\Pu;

use Anakeen\SmartHooks;

class TstExportCollection extends \Anakeen\SmartElement

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

