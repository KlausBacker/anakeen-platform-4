<?php

namespace Anakeen\Pu\SmartStructures;

class WTstFmtCol extends \SmartStructure\Wdoc
{
    public $attrPrefix = "WAN";

    const E1 = "E1";
    const E2 = "E2";
    const E3 = "E3";
    const T1 = "T1";
    public $firstState = self::E1;
    public $transitions = array(
        self::T1 => array()
    );

    public $cycle = array(
        array(
            "e1" => self::E1,
            "e2" => self::E2,
            "t" => self::T1
        ),
        array(
            "e1" => self::E2,
            "e2" => self::E3,
            "t" => self::T1
        )
    );


    public $stepLabels = [
        self::E1 => ["activity" => "Activity E1"],
        self::E2 => ["activity" => "Activity E2"]
    ];
}
