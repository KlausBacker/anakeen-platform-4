<?php

namespace Anakeen\Pu\SmartStructures;

class TestStaticDefault
{
    public static function multiAdd()
    {
        $tx = func_get_args();
        
        return array_sum($tx);
    }
    
    public static function semiColumnConcat()
    {
        $tx = func_get_args();
        
        return implode(':', $tx);
    }

    public static function stringOne() {
        return "one";
    }
}

