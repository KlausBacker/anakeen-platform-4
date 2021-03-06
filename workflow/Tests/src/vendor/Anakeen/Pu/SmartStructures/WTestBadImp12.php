<?php
/*
 * @author Anakeen
 * @package FDL
*/


namespace Anakeen\Pu\SmartStructures;

class WTestBadImp12 extends \SmartStructure\Wdoc
{
    var $attrPrefix = "WTST";
    const alive = "alive"; # N_("alive")
    const dead = "dead or not"; # N_("dead")// <-- STATE SYNTAX NAME ERROR
    const transfered = "transfered"; # N_("transfered")
    const sick = "sick"; # N_("sick")
    const convalescent = "convalescent"; # N_("convalescent")
    const T1 = "T1"; # N_("T1")
    const Tsick = "Tsick"; # N_("Tsick")
    const Tconvalescent = "Tconvalescent"; # N_("Tconvalescent")
    const T3 = "T3"; # N_("T3")
    var $firstState = self::alive;
    public $transitions = array(
        self::T1 => array() ,
        self::Tsick => array(
            "m1" => "SendMailToVeto",
            "ask" => array(
                "wan_idveto",
                "wan_veto"
            ) ,
            "nr" => true
        ) ,
        self::Tconvalescent => array(
            "m1" => "toHealthCard",
            "nr" => "not a bool"
        ) ,
        
        self::T3 => array()
    );
    
    var $cycle = array(
        array(
            "e1" => self::alive,
            "e2" => self::sick,
            "t" => self::Tsick
        ) ,
        
        array(
            "e1" => self::alive,
            "e3" => self::transfered, // <-- TRANSITION SYNTAX NAME ERROR
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::convalescent,
            "e2" => self::dead,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::sick,
            "e2" => self::convalescent,
            "t" => self::Tconvalescent
        ) ,
        
        array(
            "e1" => self::convalescent,
            "e2" => self::alive
        ) ,
        
        array(
            "e1" => self::sick,
            "e2" => self::dead,
            "t" => "T3"
        )
    );
    
    public $stepLabels = array(
        'zou' => ["activity"=>'not correct state']
    );
    
    function SendMailToVeto($newstate)
    {
    }
    
    function toHealthCard($newstate)
    {
    }
}
