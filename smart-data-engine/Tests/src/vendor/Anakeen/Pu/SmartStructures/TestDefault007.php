<?php

namespace Anakeen\Pu\SmartStructures;

use Anakeen\Core\Utils\Postgres;

class TestDefault007 extends \Anakeen\SmartElement
{
    public function isOne()
    {
        return 1;
    }

    
    public function oneMore($x)
    {
        return intval($x) + 1;
    }

    
    public function simpleAdd(...$args)
    {
        return array_sum($args);
    }
    
    public function commaConcat(...$args)
    {
        return implode(',', $args);
    }

    
    public function completeMyArray()
    {
        $t[] = array(
            "tst_text2" => "First",
            "tst_number2" => 10,
            "tst_docm2" => [9,11]
        );
        $t[] = array(
            "tst_text2" => "Second",
            "tst_number2" => 20,
            "tst_docm2" => [12,13]
        );
        return $t;
    }
}
