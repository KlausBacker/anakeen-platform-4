<?php

namespace Anakeen\Pu\SmartStructures;

class WTestControlMX2 extends \SmartStructure\Wdoc
{
    public $attrPrefix = "WMX";

    const SA = "SA";
    const SB = "SB";
    const T1 = "T1";
    public $firstState = self::SA;
    public $transitions = array(
        self::T1 => array(
        )
    );

    public $cycle = array(
        array(
            "e1" => self::SA,
            "e2" => self::SB,
            "t" => self::T1
        )
    );

    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);


        $this->getTransition("T1")
            ->setM0(function ($newstate) {
                return $this->t1m0($newstate);
            })->setM1(function ($newstate) {
                return $this->t1m1($newstate);
            })->setM2(function ($newstate) {
                return $this->t1m2($newstate);
            })->setM3(function ($newstate) {
                return $this->t1m3($newstate);
            });
    }

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
