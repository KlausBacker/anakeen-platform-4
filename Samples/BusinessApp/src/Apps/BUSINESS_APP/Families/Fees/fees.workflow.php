<?php


namespace Sample\BusinessApp;

use \Dcp\AttributeIdentifiers\BA_FEES as FeesAttr;
use \Dcp\AttributeIdentifiers\BA_WFEES as WFeesAttr;
use Dcp\HttpApi\V1\DocManager\DocManager;

class WFees extends \Dcp\Family\Wdoc
{
    public $attrPrefix = "WFEE";

    //region States
    const E_BA_DRAFT = "e_ba_draft"; # N_("e_ba_draft")
    const E_BA_FILLED = "e_ba_filled"; # N_("e_ba_filled")
    const E_BA_VALIDATED = "e_ba_validated"; # N_("e_ba_validated")
    const E_BA_INTEGRATED = "e_ba_integrated"; # N_("e_ba_integrated")
    //endregion

    //region Activities
    public $stateactivity = array(
      self::E_BA_DRAFT => "e_ba_writing",# N_("e_ba_writing")
      self::E_BA_FILLED => "e_ba_validating", # N_("e_ba_validating")
      self::E_BA_VALIDATED => "e_ba_integrating" # N_("e_ba_integrating")
    );
    //endregion

    //region Transitions
    const T_BA_SEND = "t_ba_send"; # N_("t_ba_send")
    const T_BA_VALID= "t_ba_valid"; # N_("t_ba_valid")
    const T_BA_INTEGRATE= "t_ba_integrate"; # N_("t_ba_integrate")
    //endregion


    public $transitions = array(
        self::T_BA_SEND => array(
            "ask" => array("wfee_user_valid"),
            "m1" => "handleUserResponse",
            "nr" => true
        ),
        self::T_BA_VALID => array(
            "nr" => true
        ),
        self::T_BA_INTEGRATE => array(
            "nr" => true
        )
    );

    public $firstState=self::E_BA_DRAFT;
    public $cycle = array(
        array(
            "e1" => self::E_BA_DRAFT,
            "e2" => self::E_BA_FILLED,
            "t" => self::T_BA_SEND
        ),
        array(
            "e1" => self::E_BA_FILLED,
            "e2" => self::E_BA_VALIDATED,
            "t" => self::T_BA_VALID
        ),
        array(
            "e1" => self::E_BA_VALIDATED,
            "e2" => self::E_BA_INTEGRATED,
            "t" => self::T_BA_INTEGRATE
        )
    );

    public function handleUserResponse($nextStep, $currentStep, $confirmationMessage) {
        return "";
    }
}
