<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\Ddui;

class WflAlltype extends \Dcp\Family\WDoc
{
    public $attrPrefix = "TST";
    public $firstState = self::etat_created;
    public $viewlist = "none";

    //region States(adjectif)
    const etat_created    = "tst_etat_cree";
    const etat_redacted   = "tst_etat_redige";
    const etat_verified   = "tst_etat_verifie";
    const etat_diffused   = "tst_etat_diffuse";
    const etat_archived   = "tst_etat_archive";
    const etat_abandonned = "tst_etat_abandonne";
    //endregion

    //region Transitions(participe participe passé)
    const transition_created_redacted    = "tst_transition_cree_redige";
    const transition_redacted_created    = "tst_transition_redige_cree";
    const transition_redacted_verified   = "tst_transition_redige_verifie";
    const transition_verified_created    = "tst_transition_verifie_cree";
    const transition_verified_diffused   = "tst_transition_verifie_diffuse";
    const transition_diffused_archived   = "tst_transition_diffuse_archive";
    const transition_created_abandonned  = "tst_transition_cree_abandonne";
    const transition_redacted_abandonned = "tst_transition_redige_abandonne";
    const transition_verified_abandonned = "tst_transition_verifie_abandonne";
    //endregion

    //activité
    public $stateactivity = array(
        self::etat_created    => "tst_activite_etat_cree",
        self::etat_redacted   => "tst_activite_etat_redige",
        self::etat_verified   => "tst_activite_etat_verifie",
        self::etat_diffused   => "tst_activite_etat_diffuse",
        self::etat_archived   => "tst_activite_etat_archive",
        self::etat_abandonned => "tst_activite_etat_abandonne"
    );


    public $transitions = array(
        self::transition_created_redacted    =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_redacted_created    =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_redacted_verified   =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_verified_created    =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_verified_diffused   =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_diffused_archived   =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_created_abandonned  =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_redacted_abandonned =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        ),
        self::transition_verified_abandonned =>array(
            "m0" => "",
            "m1" => "",
            "m2" => "",
            "m3" => "",
            "nr" => true
        )
    );

    public $cycle = array(

        array(
            "e1" => self::etat_created,
            "e2" => self::etat_redacted,
            "t"  => self::transition_created_redacted
        ),
        array(
            "e1" => self::etat_redacted,
            "e2" => self::etat_created,
            "t"  => self::transition_redacted_created
        ),
        array(
            "e1" => self::etat_redacted,
            "e2" => self::etat_verified,
            "t"  => self::transition_redacted_verified
        ),
        array(
            "e1" => self::etat_verified,
            "e2" => self::etat_created,
            "t"  => self::transition_verified_created
        ),
        array(
            "e1" => self::etat_verified,
            "e2" => self::etat_diffused,
            "t"  => self::transition_verified_diffused
        ),
        array(
            "e1" => self::etat_diffused,
            "e2" => self::etat_archived,
            "t"  => self::transition_diffused_archived
        ),
        array(
            "e1" => self::etat_created,
            "e2" => self::etat_abandonned,
            "t"  => self::transition_created_abandonned
        ),
        array(
            "e1" => self::etat_redacted,
            "e2" => self::etat_abandonned,
            "t"  => self::transition_redacted_abandonned
        ),
        array(
            "e1" => self::etat_verified,
            "e2" => self::etat_abandonned,
            "t"  => self::transition_verified_abandonned
        )
    );
    private function i18n () {
        // states
        $i18n=_("tst_etat_cree");
        $i18n=_("tst_etat_redige");
        $i18n=_("tst_etat_verifie");
        $i18n=_("tst_etat_diffuse");
        $i18n=_("tst_etat_archive");
        $i18n=_("tst_etat_abandonne");
        // tra_sitst:tion_
        $i18n=_("tst_transition_cree_redige");
        $i18n=_("tst_transition_redige_cree");
        $i18n=_("tst_transition_redige_verifie");
        $i18n=_("tst_transition_verifie_cree");
        $i18n=_("tst_transition_verifie_diffuse");
        $i18n=_("tst_transition_diffuse_archive");
        $i18n=_("tst_transition_cree_abandonne");
        $i18n=_("tst_transition_redige_abandonne");
        $i18n=_("tst_transition_verifie_abandonne");
        // act_vities
        $i18n=_("tst_activite_etat_cree");
        $i18n=_("tst_activite_etat_redige");
        $i18n=_("tst_activite_etat_verifie");
        $i18n=_("tst_activite_etat_diffuse");
        $i18n=_("tst_activite_etat_archive");
        $i18n=_("tst_activite_etat_abandonne");
    }

}