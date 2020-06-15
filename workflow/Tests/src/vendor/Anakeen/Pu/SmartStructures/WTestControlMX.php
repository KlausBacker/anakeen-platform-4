<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Pu\SmartStructures;

class WTestControlMX extends \SmartStructure\Wdoc
{
    public $attrPrefix = "WMX";

    const SA = "SA";
    const SB = "SB";
    const T1 = "T1";
    public $firstState = self::SA;
    public $transitions = array(
        self::T1 => array(
            "m0" => "t1m0",
            "m1" => "t1m1",
            "m2" => "t1m2",
            "m3" => "t1m3",
        )
    );

    public $cycle = array(
        array(
            "e1" => self::SA,
            "e2" => self::SB,
            "t" => self::T1
        )
    );

    public function t1m0($newstate)
    {
        return $this->updateT(0);
    }

    public function t1m1($newstate)
    {
        return $this->updateT(1);
    }

    public function t1m2($newstate)
    {
        return $this->updateT(2);
    }

    public function t1m3($newstate)
    {
        return $this->updateT(3);
    }

    protected function updateT($m)
    {
        $m1 = $m + 1;
        $err = $this->doc->setValue("tst_title", $this->doc->getRawValue("tst_title") . "-M$m-");
        $err .= $this->doc->setValue("tst_number", intval($this->doc->getRawValue("tst_number")) + $m1);
        $err .= $this->doc->setValue(
            "tst_date",
            date('Y-m-d', strtotime($this->doc->getRawValue("tst_date") . " + $m1 days"))
        );
        return $err;
    }
}
