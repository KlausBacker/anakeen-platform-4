<?php

namespace Anakeen\Pu\Config;

class DefaultBehavior extends \Anakeen\SmartElement
{
    public function three()
    {
        return 3;
    }

    public static function addingNumbers(...$n)
    {
        return array_sum($n);
    }
}
