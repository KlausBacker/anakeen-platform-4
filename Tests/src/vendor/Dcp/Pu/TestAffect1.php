<?php


namespace Dcp\Pu;

use Anakeen\SmartHooks;

class TestAffect1 extends \Anakeen\SmartStructures\Document
{
    protected $one = 0;
    protected $two = 0;


    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::PREAFFECT, function () {
            $this->one++;
        });
    }

    public function getOne()
    {
        return $this->one;
    }

    public function getTwo()
    {
        return $this->two;
    }
}