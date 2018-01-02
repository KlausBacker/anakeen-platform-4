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
    const E_BA_DRAFT_EXCEED = "e_ba_draft_exceed"; # N_("e_ba_draft_exceed")
    const E_BA_FILLED = "e_ba_filled"; # N_("e_ba_filled")
    const E_BA_VALIDATED = "e_ba_validated"; # N_("e_ba_validated")
    const E_BA_INTEGRATED = "e_ba_integrated"; # N_("e_ba_integrated")
    //endregion

    //region Activities
    public $stateactivity = array(
      self::E_BA_DRAFT => "e_ba_writing",# N_("e_ba_writing")
      self::E_BA_DRAFT_EXCEED => "e_ba_exceed_approving", # N_("e_ba_exceed_approving")
      self::E_BA_FILLED => "e_ba_validating", # N_("e_ba_validating")
      self::E_BA_VALIDATED => "e_ba_integrating" # N_("e_ba_integrating")
    );
    //endregion

    //region Transitions
    const T_BA_SEND = "t_ba_send"; # N_("t_ba_send")
    const T_BA_EXCEED_DEMAND = "t_ba_exceed_demand"; # N_("t_ba_exceed_demand")
    const T_BA_EXCEED_RESPONSE = "t_ba_exceed_response"; # N_("t_ba_exceed_response")
    const T_BA_VALID= "t_ba_valid"; # N_("t_ba_valid")
    const T_BA_INTEGRATE= "t_ba_integrate"; # N_("t_ba_integrate")
    //endregion


    public $transitions = array(
        self::T_BA_SEND => array(
            "m0" => "checkSend",
            "ask" => array(WFeesAttr::wfee_user_valid),
            "nr" => true
        ),
        self::T_BA_EXCEED_DEMAND => array(
            "m0" => "checkExceeds",
            "ask" => array(WFeesAttr::wfee_user_valid),
            "nr" => true
        ),
        self::T_BA_EXCEED_RESPONSE => array(
            "ask" => array(WFeesAttr::wfee_exceed_decision),
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
          "e1" => self::E_BA_DRAFT,
          "e2" => self::E_BA_DRAFT_EXCEED,
          "t" => self::T_BA_EXCEED_DEMAND,
        ),
        array(
            "e1" => self::E_BA_DRAFT_EXCEED,
            "e2" => self::E_BA_DRAFT,
            "t" => self::T_BA_EXCEED_RESPONSE
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

    public function checkExceeds($nextStep, $currentStep, $confirmationMessage) {
        $exceeds = $this->getExceedsOutgoings();
        $exceedDecision = $this->getRawValue(WFeesAttr::wfee_exceed_decision);
        $msg = "";
        if (empty($exceeds)) {
            $msg .= "Your fee note is available for sending to validation";
            return ___($msg, 'BA_WFEES');
        }
        if ($exceedDecision == 1) {
            $msg .= "Your exceed demand has been approved. You can sign and send your fee note";
            return ___($msg, 'BA_WFEES');
        }
        return $msg;
    }

    public function checkSend($nextStep, $currentStep, $confirmationMessage) {
        $exceeds = $this->getExceedsOutgoings();
        $exceedDecision = $this->getRawValue(WFeesAttr::wfee_exceed_decision);
        $msg = "";
        if (!empty($exceeds)) {
            if ($exceedDecision == 1) { // Exceed demand accepted
                return $msg;
            }
            $msg .= "You exceed the amounts for some categories. Check amounts or make an exceed demand";
            return ___($msg, 'BA_WFEES');
        }
        return $msg;
    }

    protected function getExceedsOutgoings() {
        $doc = $this->doc;
        $outgoings = $doc->getArrayRawValues(FeesAttr::fee_t_all_exp);
        return array_filter($outgoings, function($o) {
            if ($o[FeesAttr::fee_exp_exceed] == "yes") {
                return true;
            }
            return false;
        });
    }


}
