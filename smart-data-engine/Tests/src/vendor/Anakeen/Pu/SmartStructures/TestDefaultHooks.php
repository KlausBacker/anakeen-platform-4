<?php

namespace Anakeen\Pu\SmartStructures;

use Anakeen\Core\Utils\Postgres;

class TestDefaultHooks extends \Anakeen\SmartElement
{
    public function isOne()
    {
        return 1;
    }
    
    public function addAllPlusOne($ar1, $ar2, $val)
    {
        return $ar1 + $ar2 + $val + 1;
       // return Postgres::arrayToString([$ar1 + $ar2 + $val + 1]);
    }
    
    public function oneMore($x)
    {
        return intval($x) + 1;
    }

    public function oneArrayMore($x)
    {
        return intval($x) + 1;
        //return Postgres::arrayToString([intval($x) + 1]);
    }
    public function itself($x)
    {
        return $x;
    }
    
    public function simpleAdd(...$args)
    {
        return array_sum($args);
    }
    
    public function commaConcat()
    {
        $tx = func_get_args();
        
        return implode(',', $tx);
    }
    
    public function searchOnFamily()
    {
        $s = new \Anakeen\Search\Internal\SearchSmartData($this->dbaccess, "TST_DEFAULTFAMILY1");
        $s->search();
        return $s->count();
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
    public function completeWrongArray()
    {
        $t = "not an array is a string";
        return $t;
    }
    public function completeWrongAttributeArray()
    {
        $t[] = array(
            "tst_text2" => "First",
            "tst_number2" => "pi",
            "tst_docm2" => [9,11]
        );
        $t[] = array(
            "tst_text2" => "Seven",
            "tst_number2" => 20,
            "tst_docm2" => [12,13]
        );
        return $t;
    }
}
