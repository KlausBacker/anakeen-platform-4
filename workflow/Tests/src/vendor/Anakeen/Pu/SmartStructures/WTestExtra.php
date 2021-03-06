<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Pu\SmartStructures;

class WTestExtra extends \SmartStructure\Wdoc
{
    public $attrPrefix = "WAN";

    const alive = "alive"; # N_("alive")
    const dead = "dead"; # N_("dead")
    const transfered = "transfered"; # N_("transfered")
    const sick = "sick"; # N_("sick")
    const convalescent = "convalescent"; # N_("convalescent")
    const T1 = "T1"; # N_("T1")
    const Tsick = "Tsick"; # N_("Tsick")
    const Tconvalescent = "Tconvalescent"; # N_("Tconvalescent")
    const T3 = "T3"; # N_("T3")
    public $firstState = self::alive;
    public $transitions
        = array(
            self::T1 => array(),
            self::Tsick => array(
                "m1" => "SendMailToVeto",
                "ask" => array(
                    "wtst_refus",
                ),
                "nr" => true
            ),
            self::Tconvalescent => array(
                "m1" => "toHealthCard"
            ),
            self::T3 => array()
        );

    public $cycle
        = array(
            array(
                "e1" => self::alive,
                "e2" => self::sick,
                "t" => self::Tsick
            ),

            array(
                "e1" => self::alive,
                "e2" => self::transfered,
                "t" => self::T1
            ),

            array(
                "e1" => self::convalescent,
                "e2" => self::dead,
                "t" => self::T1
            ),

            array(
                "e1" => self::sick,
                "e2" => self::convalescent,
                "t" => self::Tconvalescent
            ),

            array(
                "e1" => self::convalescent,
                "e2" => self::alive,
                "t" => self::T1
            ),

            array(
                "e1" => self::sick,
                "e2" => self::dead,
                "t" => "T3"
            )
        );

    function SendMailToVeto($newstate)
    {
    }

    function toHealthCard($newstate)
    {
    }
}
