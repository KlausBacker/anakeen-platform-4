<?php


namespace Dcp\Pu;

use Anakeen\SmartHooks;

/**
 * Class TestAffect1
 * @package Dcp\Pu
 */
class TestAffect2 extends \SmartStructure\Tst_Affect1
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::PREAFFECT, function () {
            $this->two++;
        })->addListener(SmartHooks::POSTAFFECT, function () {
            $this->two++;
        });
    }


}