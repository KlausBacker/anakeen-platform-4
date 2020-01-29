<?php /** @noinspection PhpUnusedParameterInspection */

/**
 * Workflow Class Document
 *
 */

namespace Anakeen\SmartStructures\Wdoc;

use Anakeen\Core\Account;
use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\SmartStructure\DocAttr;
use Anakeen\LogManager;
use Anakeen\SmartHooks;
use Anakeen\SmartStructures\Timer\TimerHooks;
use Anakeen\Exception;
use Anakeen\Core\SmartStructure\SmartFieldAbsoluteOrder;

class WDocHooks extends \Anakeen\Core\Internal\SmartElement
{
    /**
     * WDoc has its own special access depend on transition
     * by default the three access are always set
     *
     * @var array
     */
    protected static $basicAcls =[ "view", "edit", "delete"];

    public $usefor = 'SW';
    public $defDoctype = 'W';
    public $defClassname = 'WDoc';
    public $attrPrefix = "WF"; // prefix attribute

    /**
     * state's activities labels
     *
     * @var array
     */
    public $stepLabels = array(); // label of steps
    // --------------------------------------------------------------------
    //----------------------  TRANSITION DEFINITION --------------------
    public $transitions = array(); // set by childs classes
    public $cycle = array(); // set by childs classes
    public $autonext = array(); // set by childs classes
    public $firstState = ""; // first state in workflow
    public $viewnext = "list"; // view interface as select list may be (list|button)
    public $nosave = array(); // states where it is not permitted to save and stay (force next state)

    /**
     * @var array
     */
    public $states = null;
    protected $askValue;
    /**
     * @var WDocHooks|null
     */
    private $pdoc = null;
    /**
     * document instance
     *
     * @var \Anakeen\Core\Internal\SmartElement
     */
    public $doc = null;

    public $graphModelName;

    const TIMER_PERSISTENT = "persistent";
    const TIMER_VOLATILE = "volatile";
    const TIMER_UNATTACH = "unattach";

    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // first construct acl array
        $this->setTransitionsAcls();

        if (isset($this->fromid)) {
            // it's a profil model itself
            $this->defProfFamId = $this->fromid;
        }

        \Anakeen\Core\Internal\SmartElement::__construct($dbaccess, $id, $res, $dbid);
    }


    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $this->getStates();
            if ($this->isChanged()) {
                $this->modify();
            }
        });
    }

    protected function setTransitionsAcls()
    {
        $this->acls = self::$basicAcls;
        if (is_array($this->transitions)) {
            $this->extendedAcls=[];
            foreach ($this->transitions as $k => $trans) {
                $this->extendedAcls[$k] = array(
                    "name" => $k,
                    "description" => $this->getTransitionLabel($k)
                );
                $this->acls[] = $k;
            }
        }
    }

    /**
     * affect document instance
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc document to use for workflow
     * @param bool $force set to true to force a doc reset
     *
     * @return void
     */
    public function set(\Anakeen\Core\Internal\SmartElement & $doc, $force = false)
    {
        if ((!isset($this->doc)) || ($this->doc->id != $doc->id) || $force) {
            $this->doc = &$doc;
            if (($doc->doctype !== 'C') && ($doc->state == "")) {
                $doc->state = $this->getFirstState();
                $this->changeProfil($doc->state);
                $this->changeCv($doc->state);
            }
        }
    }

    public function getFirstState()
    {
        return $this->firstState;
    }

    /**
     * Get the current associated smart element
     *
     * @return \Anakeen\Core\Internal\SmartElement
     */
    public function getSmartElement()
    {
        return $this->doc;
    }

    /**
     * change profil according to state
     *
     * @param string $newstate new \state of document
     *
     * @return string
     */
    public function changeProfil($newstate)
    {
        $err = '';
        if ($newstate != "") {
            $profid = $this->getRawValue($this->_aid("_ID", $newstate));
            if (!is_numeric($profid)) {
                $profid = \Anakeen\Core\SEManager::getIdFromName($profid);
            }
            if ($profid > 0) {
                // change only if new profil
                $err = $this->doc->accessControl()->setProfil($profid);
            }

            $fallid = $this->getRawValue($this->_aid("_FALLID", $newstate));
            if (!is_numeric($fallid)) {
                $fallid = \Anakeen\Core\SEManager::getIdFromName($profid);
            }
            if ($fallid > 0) {
                // change only if new field access
                $this->doc->accessControl()->setFallid($fallid);
            }
        }
        return $err;
    }

    /**
     * change cv according to state
     *
     * @param string $newstate new \state of document
     */
    public function changeCv($newstate)
    {
        if ($newstate != "") {
            $cvid = ($this->getRawValue($this->_aid("_CVID", $newstate)));
            if (!is_numeric($cvid)) {
                $cvid = \Anakeen\Core\SEManager::getIdFromName($cvid);
            }
            if ($cvid > 0) {
                // change only if set
                $this->doc->cvid = $cvid;
            } else {
                $fdoc = $this->doc->getFamilyDocument();
                $this->doc->cvid = $fdoc->ccvid;
            }
        }
    }

    private function _aid($fix, $state)
    {
        return strtolower($this->attrPrefix . $fix . str_replace(":", "_", $state));
    }

    public function getStateLabel($state)
    {
        if (!$state) {
            return $state;
        }
        if ($this->graphModelName) {
            $label = ___($state, $this->graphModelName . ":state");
            if ($label !== $state) {
                return $label;
            }
        }
        if (!empty($this->stepLabels[$state])) {
            return $this->stepLabels[$state]["state"] ?? $state;
        }
        return $state;
    }

    public function getTransitionLabel($transitionId)
    {
        if ($this->graphModelName) {
            $label = ___($transitionId, $this->graphModelName . ":transition");
            if ($label === $transitionId) {
                $label = $this->transitions[$transitionId]["label"] ?? '';
                if (!$label) {
                    $label = $transitionId;
                }
            }
            return $label;
        }
        return _($transitionId);
    }

    /**
     * get the profile id according to state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateProfil($state)
    {
        return $this->getRawValue($this->_aid("_id", $state));
    }

    public function setStateProfil($state, $value)
    {
        return $this->setValue($this->_aid("_id", $state), $value);
    }

    /**
     * get the field access list id according to state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateFall($state)
    {
        return $this->getRawValue($this->_aid("_fallid", $state));
    }

    public function setStateFall($state, $value)
    {
        return $this->setValue($this->_aid("_fallid", $state), $value);
    }

    /**
     * get the attribute id for profile id according to state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateProfilAttribute($state)
    {
        return $this->_aid("_id", $state);
    }

    /**
     * get the mask id according to state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateMask($state)
    {
        return $this->getRawValue($this->_aid("_mskid", $state));
    }

    public function setStateMask($state, $value)
    {
        return $this->setValue($this->_aid("_mskid", $state), $value);
    }

    /**
     * get the view control id according to state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateViewControl($state)
    {
        return $this->getRawValue($this->_aid("_cvid", $state));
    }

    public function setStateViewControl($state, $value)
    {
        return $this->setValue($this->_aid("_cvid", $state), $value);
    }

    /**
     * get the timers ids according to state
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateTimer($state)
    {
        return $this->getRawValue($this->_aid("_tmid", $state));
    }

    public function setStateTimer($state, $timerref)
    {
        return $this->setValue($this->_aid("_tmid", $state), $timerref);
    }

    /**
     * get the timers ids according to transition
     *
     * @param string $transName transition name
     *
     * @return array
     */
    public function getTransitionTimers($transName)
    {
        $persistents = $this->getMultipleRawValues($this->_aid("_trans_pa_tmid", $transName));
        $volatiles = $this->getMultipleRawValues($this->_aid("_trans_tmid", $transName));
        $todetach = $this->getMultipleRawValues($this->_aid("_trans_pu_tmid", $transName));
        $timers = [];
        foreach ($persistents as $timerId) {
            $timers[] = [
                "type" => self::TIMER_PERSISTENT,
                "id" => $timerId
            ];
        }
        foreach ($volatiles as $timerId) {
            $timers[] = [
                "type" => self::TIMER_VOLATILE,
                "id" => $timerId
            ];
        }
        foreach ($todetach as $timerId) {
            $timers[] = [
                "type" => self::TIMER_UNATTACH,
                "id" => $timerId
            ];
        }
        return $timers;
    }

    public function setTransitionTimers($transName, $value, $type)
    {
        switch ($type) {
            case self::TIMER_PERSISTENT:
                $key = $this->_aid("_trans_pa_tmid", $transName);
                break;
            case self::TIMER_VOLATILE:
                $key = $this->_aid("_trans_tmid", $transName);
                break;
            case self::TIMER_UNATTACH:
                $key = $this->_aid("_trans_pu_tmid", $transName);
                break;
            default:
                throw new Exception(sprintf("Invalid timer type \"%s\"", $type));
        }
        return $this->setValue($key, $value);
    }

    /**
     * get the mail ids according to transition
     *
     * @param string $transName transition name
     *
     * @return array
     */
    public function getTransitionMailTemplates($transName)
    {
        return $this->getMultipleRawValues($this->_aid("_trans_mtid", $transName));
    }

    public function setTransitionMailTemplates($transName, $value)
    {
        return $this->setValue($this->_aid("_trans_mtid", $transName), $value);
    }

    /**
     * get the mail templates ids according to state
     *
     * @param string $state
     *
     * @return array
     */
    public function getStateMailTemplate($state)
    {
        return $this->getMultipleRawValues($this->_aid("_mtid", $state));
    }

    public function setStateMailTemplate($state, $mails)
    {
        return $this->setValue($this->_aid("_mtid", $state), $mails);
    }


    /**
     * Return transition object referenced by name
     *
     * @param string $transitionName
     *
     * @return Transition|null
     */
    public function getTransition(string $transitionName): ?Transition
    {
        if (!isset($this->transitions[$transitionName])) {
            return null;
        }
        return new Transition($this, $transitionName);
    }

    /**
     * @param string $transitionName
     * @param string $mIndex m0, m1, m2, m3
     *
     * @param array $args the three arguments $nextState, $previousState, $comment
     *
     * @return false|string error message (empty if no errors)
     */
    public function executeTransitionM(string $transitionName, string $mIndex, ...$args)
    {
        if (isset($this->transitions[$transitionName])) {
            $tr = $this->transitions[$transitionName];
            if (!empty($tr[$mIndex])) {
                if (is_callable($tr[$mIndex])) {
                    $err = call_user_func($tr[$mIndex], ...$args);
                } else {
                    if (!method_exists($this, $tr[$mIndex])) {
                        return (sprintf(
                            ___("[%s] the method '%s' is not known for the object class %s", "sde"),
                            $mIndex,
                            $tr[$mIndex],
                            get_class($this)
                        ));
                    }
                    $err = call_user_func(array(
                        $this,
                        $tr[$mIndex]
                    ), ...$args);
                }
                return $err;
            }
        }
        return false;
    }

    /**
     * create of parameters attributes of workflow
     *
     * @param int $cid
     *
     * @return string error message
     */
    public function createProfileAttribute($cid = 0)
    {
        if (!$cid) {
            if ($this->doctype == 'C') {
                $cid = $this->id;
            } else {
                $cid = $this->fromid;
            }
        }

        \Anakeen\Core\DbManager::setMasterLock(true);
        // delete old attributes before
        $this->query(sprintf("delete from docattr where docid=%d  and options ~ 'autocreated=yes'", intval($cid)));
        $this->getStates();
        $ordered = 1;
        foreach ($this->states as $k => $state) {
            // --------------------------
            // frame
            $aidframe = $this->_aid("_FR", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidframe
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = "frame";
            $oattr->id = $aidframe;
            $oattr->frameid = "wf_tab_states";
            $oattr->labeltext = sprintf(_("parameters for %s step"), $this->getStateLabel($state));
            $oattr->link = "";
            $oattr->phpfunc = "";
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;

            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // profil id
            $aidprofilid = $this->_aid("_ID", $state); //strtolower($this->attrPrefix."_ID".strtoupper($state));
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidprofilid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("PDOC")';
            $oattr->id = $aidprofilid;
            $oattr->labeltext = sprintf(_("%s profile"), $this->getStateLabel($state));
            $oattr->link = "";
            $oattr->frameid = $aidframe;
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;

            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }

            // profil id
            $aidprofilid = $this->_aid("_FALLID", $state); //strtolower($this->attrPrefix."_ID".strtoupper($state));
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidprofilid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("FIELDACCESSLAYERLIST")';
            $oattr->id = $aidprofilid;
            $oattr->labeltext = sprintf(_("%s field access list"), $this->getStateLabel($state));
            $oattr->link = "";
            $oattr->frameid = $aidframe;
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;

            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }

            // --------------------------
            // mask id
            $aid = $this->_aid("_MSKID", $state);

            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("MASK")';
            $oattr->id = $aid;
            $oattr->labeltext = sprintf(_("%s mask"), $this->getStateLabel($state));
            $oattr->link = "";
            $oattr->frameid = $aidframe;
            $oattr->elink = '';
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // state color
            $aid = $this->_aid("_COLOR", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = "color";
            $oattr->link = "";
            $oattr->phpfile = "";
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->phpfunc = "";
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->labeltext = sprintf(_("%s color"), $this->getStateLabel($state));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // CV link
            $aid = $this->_aid("_CVID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("CVDOC")';
            $oattr->link = "";
            $oattr->elink = '';
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;

            $oattr->labeltext = sprintf(_("%s cv"), $this->getStateLabel($state));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // Mail template link
            $aid = $this->_aid("_MTID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("MAILTEMPLATE")';
            $oattr->link = "";
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->options = "multiple=yes|autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->elink = '';
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s mail template"), $this->getStateLabel($state));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            //  Timer link
            $aid = $this->_aid("_TMID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->id = $aid;
            $oattr->elink = '';
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s timer"), $this->getStateLabel($state));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
        }

        foreach ($this->transitions as $k => $trans) {
            // --------------------------
            // frame
            $aidframe = $this->_aid("_TRANS_FR", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidframe
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = "frame";
            $oattr->id = $aidframe;
            $oattr->frameid = "wf_tab_transitions";
            $oattr->labeltext = sprintf(_("parameters for %s transition"), $this->getTransitionLabel($k));
            $oattr->link = "";
            $oattr->phpfunc = "";
            $oattr->options = "autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // Mail template link
            $aid = $this->_aid("_TRANS_MTID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("MAILTEMPLATE")';
            $oattr->link = "";
            $oattr->elink = "";
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->options = "autocreated=yes|multiple=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;

            $oattr->labeltext = sprintf(_("%s mail template"), $this->getTransitionLabel($k));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // Timer link
            $aid = $this->_aid("_TRANS_TMID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->elink = "";
            $oattr->options = "autocreated=yes|multiple=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;

            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s timer"), $this->getTransitionLabel($k));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // Persistent Attach Timer link
            $aid = $this->_aid("_TRANS_PA_TMID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->elink = "";
            $oattr->options = "multiple=yes|autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;

            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s persistent timer"), $this->getTransitionLabel($k));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
            // --------------------------
            // Persistent UnAttach Timer link
            $aid = $this->_aid("_TRANS_PU_TMID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->accessibility = "ReadWrite";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->elink = "";
            $oattr->id = $aid;
            $oattr->options = "multiple=yes|autocreated=yes|relativeOrder=" . SmartFieldAbsoluteOrder::autoOrder;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s unattach timer"), $this->getTransitionLabel($k));
            if ($oattr->isAffected()) {
                $oattr->Modify();
            } else {
                $oattr->add();
            }
        }
        \Anakeen\Core\DbManager::setMasterLock(false);
        return \Anakeen\Core\SmartStructure\SmartStructureImport::refreshPhpPgDoc($this->dbaccess, $cid);
    }/** @noinspection PhpUnusedParameterInspection */

    /**
     * change state of a document
     * the method {@link set()} must be call before
     *
     * @param string $newstate the next state
     * @param string $addcomment comment to be set in history (describe why change state)
     * @param bool $force is true when it is the second passage (without interactivity)
     * @param bool $withcontrol set to false if you want to not verify control permission ot transition
     * @param bool $wm1 set to false if you want to not apply m1 methods
     * @param bool $wm2 set to false if you want to not apply m2 methods
     * @param bool $wneed set to false to not test required attributes
     * @param bool $wm0 set to false if you want to not apply m0 methods
     * @param bool $wm3 set to false if you want to not apply m3 methods
     * @param string $msg return message from m2 or m3 methods
     *
     * @return string error message, if no error empty string
     */
    public function changeState(
        $newstate,
        $addcomment = "",
        $force = false,
        $withcontrol = true,
        $wm1 = true,
        $wm2 = true,
        $wneed = true,
        $wm0 = true,
        $wm3 = true,
        &$msg = ''
    ) {
        $err = '';
        // if ($this->doc->state == $newstate) return ""; // no change => no action
        // search if possible change in concordance with transition array
        $foundFrom = false;
        $foundTo = false;
        $tname = '';
        $tr = array();
        foreach ($this->cycle as $trans) {
            if (($this->doc->state == $trans["e1"])) {
                // from state OK
                $foundFrom = true;
                if ($newstate == $trans["e2"]) {
                    $foundTo = true;
                    $tr = $this->transitions[$trans["t"]];
                    $tname = $trans["t"];
                }
            }
        }

        if (ContextManager::getCurrentUser()->id != 1) { // admin can go to any states
            if (!$foundTo) {
                return (sprintf(
                    ___("ChangeState :: the new state '%s' is not known or is not allowed from %s", "sde"),
                    $this->getStateLabel($newstate),
                    $this->getStateLabel($this->doc->state)
                ));
            }
            if (!$foundFrom) {
                return (sprintf(
                    ___("ChangeState :: the initial state '%s' is not known", "sde"),
                    $this->getStateLabel($this->doc->state)
                ));
            }
            if ($this->doc->isLocked()) {
                $lockUserId = abs($this->doc->locked);
                $lockU = new Account("", $lockUserId);
                $lockUserAccount = null;
                if ($lockU->isAffected()) {
                    $lockUserAccount = SEManager::getDocument($lockU->fid);
                }

                if (is_object($lockUserAccount) && $lockUserAccount->isAlive()) {
                    $lockUserTitle = $lockUserAccount->getTitle();
                    if ($lockUserId != ContextManager::getCurrentUser()->id) {
                        /* The document is locked by another user */
                        if ($this->doc->locked < 0) {
                            /* Currently being edited by another user */
                            return sprintf(
                                _("Could not perform transition because the document is being edited by '%s'"),
                                $lockUserTitle
                            );
                        } else {
                            /* Explicitly locked by another user */
                            return sprintf(
                                _("Could not perform transition because the document is locked by '%s'"),
                                $lockUserTitle
                            );
                        }
                    }
                }
            }
        }
        // verify if privilege granted
        if ($withcontrol) {
            $err = $this->control($tname);
        }
        if ($err != "") {
            return $err;
        }


        if ($wm0 && (!empty($tr["m0"]))) {
            // apply M0 method precondition
            try {
                $err = $this->executeTransitionM($tname, "m0", $newstate, $this->doc->state, $addcomment);
            } catch (Exception $e) {
                $err = $e->getMessage();
            }
            if ($err != "") {
                $this->doc->unlock(true);
                return (sprintf(_("Error : %s"), $err));
            }
        }

        $this->recordAskValues($tname);

        if ($wm1 && (!empty($tr["m1"]))) {
            // apply M1 method conditions
            try {
                $err = $this->executeTransitionM($tname, "m1", $newstate, $this->doc->state, $addcomment);
            } catch (Exception $e) {
                $err = $e->getMessage();
            }
            if ($err != "") {
                $this->doc->unlock(true);
                return $err;
            }
        }
        // verify if completed doc
        if ($wneed) {
            $err = $this->doc->isCompleteNeeded();
            if ($err != "") {
                return $err;
            }
        }
        // change the state
        $oldstate = $this->doc->state == "" ? " " : $this->doc->state;
        $previousState = $this->doc->state;
        $this->doc->state = $newstate;
        $this->changeProfil($newstate);
        $this->changeCv($newstate);
        $this->doc->disableAccessControl();
        $err = $this->doc->modify(); // don't control edit permission
        if ($err != "") {
            return $err;
        }

        $revcomment = sprintf(
            _("change state : %s to %s"),
            $this->getStateLabel($oldstate),
            $this->getStateLabel($newstate)
        );
        if ($addcomment != "") {
            $this->doc->addHistoryEntry($addcomment);
        }

        $revcomment .= $this->recordAskHistory($tname);
        $incumbentName = \Anakeen\Core\ContextManager::getCurrentUser()->getIncumbentPrivilege($this, $tname);
        if ($incumbentName) {
            $revcomment = sprintf(_("(substitute of %s) : "), $incumbentName) . $revcomment;
        }
        $err = $this->doc->revise($revcomment);
        if ($err != "") {
            $this->doc->disableAccessControl(); // restore old states
            $this->doc->state = $oldstate;
            $this->changeProfil($oldstate);
            $this->changeCv($oldstate);
            $err2 = $this->doc->Modify(); // don't control edit permission
            $this->doc->restoreAccessControl();

            return $err . $err2;
        }

        LogManager::notice(sprintf(___("%s new state %s", "sde"), $this->doc->title, $this->getStateLabel($newstate)));
        $this->doc->restoreAccessControl();
        // post action
        $msg2 = '';
        if ($wm2 && (!empty($tr["m2"]))) {
            $msg2 = $this->executeTransitionM($tname, "m2", $newstate, $previousState, $addcomment);

            if ($msg2) {
                $this->doc->addHistoryEntry($msg2);
            }
        }

        $this->doc->disableAccessControl();
        $this->doc->unlock(false, true);

        $msg .= $this->workflowSendMailTemplate($newstate, $addcomment, $tname);
        $this->workflowAttachTimer($newstate, $tname);
        // post action
        $msg3 = '';
        if ($wm3 && (!empty($tr["m3"]))) {
            $msg3 = $this->executeTransitionM($tname, "m3", $newstate, $previousState, $addcomment);
            if ($msg3) {
                $this->doc->addHistoryEntry($msg3);
            }
        }
        $msg .= ($msg && $msg2 ? "\n" : '') . $msg2;
        if ($msg && $msg3) {
            $msg .= "\n";
        }
        $msg .= $msg3;
        $this->doc->restoreAccessControl();
        $this->logChangeState($tname, $previousState, $msg2, $msg3);
        return $err;
    }

    /**
     * return an array of next states availables from current state
     *
     * @return array
     */
    public function getFollowingStates()
    {
        // search if following states in concordance with transition array
        if ($this->doc->locked == -1) {
            return array();
        } // no next state for revised document
        if (($this->doc->locked > 0) && ($this->doc->locked != ContextManager::getCurrentUser()->id)) {
            return array();
        } // no next state if locked by another person

        $fstate = array();
        if ($this->doc->state == "") {
            $this->doc->state = $this->getFirstState();
        }

        if (ContextManager::getCurrentUser()->id == 1) {
            return $this->getStates();
        } // only admin can go to any states from anystates
        foreach ($this->cycle as $tr) {
            if ($this->doc->state == $tr["e1"]) {
                // from state OK
                if ($this->control($tr["t"]) == "") {
                    $fstate[] = $tr["e2"];
                }
            }
        }
        return $fstate;
    }

    /**
     * return an array of all states availables for the workflow
     *
     * @return array
     */
    public function getStates()
    {
        if ($this->states === null) {
            $this->states = array();
            if (!is_array($this->cycle)) {
                throw new Exception("Workflow Corrupted Cycle");
            }
            foreach ($this->cycle as $k => $tr) {
                if (!empty($tr["e1"])) {
                    $this->states[$tr["e1"]] = $tr["e1"];
                }
                if (!empty($tr["e2"])) {
                    $this->states[$tr["e2"]] = $tr["e2"];
                }
            }
        }
        return $this->states;
    }

    /**
     * get associated color of a state
     *
     * @param string $state the state
     * @param string $def default value if not set
     *
     * @return string the color (#RGB)
     */
    public function getColor($state, $def = "")
    {
        //$acolor=$this->attrPrefix."_COLOR".($state);
        $acolor = $this->_aid("_COLOR", $state);
        return $this->getRawValue($acolor, $def);
    }

    public function setStateColor($state, $value)
    {
        return $this->setValue($this->_aid("_color", $state), $value);
    }

    /**
     * get activity (localized language)
     *
     * @param string $state the state
     * @param string $def default value if not set
     *
     * @return string the text of action
     */
    public function getActivity($state, $def = "")
    {
        if ($this->graphModelName) {
            $label = ___($state, $this->graphModelName . ":activity");
            if ($label !== $state) {
                return $label;
            }
        }
        if (!empty($this->stepLabels[$state])) {
            return $this->stepLabels[$state]["activity"] ?: $def;
        }
        return $def;
    }

    /**
     * send associated mail of a state
     *
     * @param string $state the state
     * @param string $comment reason of change state
     * @param string $tname transition name
     *
     * @return string
     */
    public function workflowSendMailTemplate($state, $comment = "", $tname = "")
    {
        $err = '';
        $tmtid = $this->getMultipleRawValues($this->_aid("_TRANS_MTID", $tname));

        $tr = ($tname) ? $this->transitions[$tname] : null;
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $keys = array();
                /**
                 * @var \SmartStructure\MAILTEMPLATE $mt
                 */
                $mt = SEManager::getDocument($mtid);
                if ($mt && $mt->isAlive()) {
                    $keys["WCOMMENT"] = nl2br($comment);
                    if (isset($tr["ask"])) {
                        foreach ($tr["ask"] as $vpid) {
                            if (is_a($vpid, BasicAttribute::class)) {
                                $keys["V_" . strtoupper($vpid)] = $this->getHtmlAttrValue($vpid);
                                $keys[strtoupper($vpid)] = $this->getRawValue($vpid);
                            } else {
                                $keys["V_" . strtoupper($vpid)] = $this->getHtmlAttrValue($vpid);
                                $keys[strtoupper($vpid)] = $this->getRawValue($vpid);
                            }
                        }
                    }
                    $err .= $mt->sendDocument($this->doc, $keys);
                }
            }
        }

        $tmtid = $this->getMultipleRawValues($this->_aid("_MTID", $state));
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $keys = array();
                $mt = SEManager::getDocument($mtid);
                /**
                 * @var \SmartStructure\MAILTEMPLATE $mt
                 */
                if ($mt && $mt->isAlive()) {
                    $keys["WCOMMENT"] = nl2br($comment);
                    if (isset($tr["ask"])) {
                        foreach ($tr["ask"] as $vpid) {
                            $keys["V_" . strtoupper($vpid)] = $this->getHtmlAttrValue($vpid);
                            $keys[strtoupper($vpid)] = $this->getRawValue($vpid);
                        }
                    }
                    $err .= $mt->sendDocument($this->doc, $keys);
                }
            }
        }
        return $err;
    }

    public function setAskValue($fieldId, $value)
    {
        $this->askValue[$fieldId] = $value;
    }

    public function getAskValue($fieldId)
    {
        return $this->askValue[$fieldId] ?? null;
    }

    protected function recordAskValues($transitionId)
    {
        if ($transitionId) {
            $asks = $this->getTransition($transitionId)->getAsks();
            foreach ($asks as $askField) {
                if ($this->getSmartElement()->getAttribute($askField->id)) {
                    if ($askField->type !== "array") {
                        $askValue = $this->getAskValue($askField->id);
                        $this->getSmartElement()->setValue($askField->id, $askValue);
                    } else {
                        $fields = $this->getSmartElement()->attributes->getArrayElements($askField->id);
                        if ($fields) {
                            foreach ($fields as $field) {
                                $askValue = $this->getAskValue($field->id);
                                $this->getSmartElement()->setValue($field->id, $askValue);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function recordAskHistory($tname)
    {
        $revcomment = '';
        $transition = $this->getTransition($tname);
        if ($transition) {
            $askes = $transition->getAsks();
            if ($askes) {
                $askLabels = [];
                foreach ($askes as $oa) {
                    $askLabels[] = $oa->getLabel();
                }
                $revcomment = sprintf("\n%s : %s", ___("Ask for", "wdoc"), implode(", ", $askLabels));
            }
        }
        return $revcomment;
    }

    protected function logChangeState($transitionId, $previousState, $msg2, $msg3)
    {
        $askValues = [];
        if ($transitionId) {
            $asks = $this->getTransition($transitionId)->getAsks();
            foreach ($asks as $askField) {
                $askValues[$askField->id] = $this->getAskValue($askField->id);
            }
        }
        $this->doc->addLog("state", array(
            "wid" => $this->initid,
            "revision" => $this->doc->revision,
            "title" => $this->doc->title,
            "state" => $this->doc->state,
            "transition" => $transitionId,
            "previousState" => $previousState,
            "asks" => $askValues,
            "m2message" => $msg2,
            "m3message" => $msg3
        ));
    }

    /**
     * attach timer to a document
     *
     * @param string $state the state
     * @param string $tname transition name
     *
     * @return string
     */
    public function workflowAttachTimer($state, $tname = "")
    {
        $err = '';
        $mtid = $this->getRawValue($this->_aid("_TRANS_TMID", $tname));

        $this->doc->unattachAllTimers($this);

        if ($mtid) {
            /**
             * @var TimerHooks $mt
             */
            $mt = SEManager::getDocument($mtid);
            if ($mt && $mt->isAlive()) {
                $err = $this->doc->attachTimer($mt, $this);
            }
        }
        // unattach persistent
        $tmtid = $this->getMultipleRawValues($this->_aid("_trans_pu_tmid", $tname));
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $mt = SEManager::getDocument($mtid);
                if ($mt && $mt->isAlive()) {
                    $err .= $this->doc->unattachTimer($mt);
                }
            }
        }

        $mtid = $this->getRawValue($this->_aid("_tmid", $state));
        if ($mtid) {
            $mt = SEManager::getDocument($mtid);
            if ($mt && $mt->isAlive()) {
                $err .= $this->doc->attachTimer($mt, $this);
            }
        }
        // attach persistent
        $tmtid = $this->getMultipleRawValues($this->_aid("_trans_pa_tmid", $tname));
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $mt = SEManager::getDocument($mtid);
                if ($mt && $mt->isAlive()) {
                    $err .= $this->doc->attachTimer($mt);
                }
            }
        }
        return $err;
    }


    /**
     * get transition array for the transition between $to and $from states
     *
     * @param string $to first state
     * @param string $from next state
     *
     * @return array|false transition array (false if not found)
     */
    public function searchTransition($from, $to)
    {
        foreach ($this->cycle as $v) {
            if (($v["e1"] == $from) && ($v["e2"] == $to)) {
                $t = $this->transitions[$v["t"]];
                $t["id"] = $v["t"];
                return $t;
            }
        }
        return false;
    }

    /**
     * explicit original doc control
     *
     * @param      $aclname
     * @param bool $strict
     *
     * @return string
     * @see \Anakeen\Core\Internal\SmartElement::control()
     */
    public function docControl($aclname, $strict = false)
    {
        return \Anakeen\Core\Internal\SmartElement::Control($aclname, $strict);
    }

    /**
     * Special control in case of dynamic controlled profil
     *
     * @param string $aclname
     * @param bool $strict set to true to not use substitute informations
     *
     * @return string error message
     */
    public function control($aclname, $strict = false)
    {
        $err = \Anakeen\Core\Internal\SmartElement::control($aclname, $strict);
        if ($err == "") {
            return $err;
        } // normal case
        if ($this->getRawValue("DPDOC_FAMID") > 0) {
            // special control for dynamic users
            if ($this->pdoc === null) {
                $pdoc = SEManager::createTemporaryDocument($this->fromid);
                $err = $pdoc->add();
                if ($err != "") {
                    return "WDoc::Control:" . $err;
                } // can't create profil
                $pdoc->accessControl()->setProfil($this->profid, $this->doc);

                $this->pdoc = &$pdoc;
            }
            $err = $this->pdoc->docControl($aclname, $strict);
        }
        return $err;
    }


    /**
     * /**
     * get value of instanced document
     *
     * @param string $attrid attribute identifier
     * @param bool $def default value if no value
     *
     * @return string return the value, false if attribute not exist or document not set
     */
    public function getInstanceValue($attrid, $def = false)
    {
        if ($this->doc) {
            return $this->doc->getRawValue($attrid, $def);
        }
        return $def;
    }

    public function getInstance()
    {
        return $this->doc;
    }

    protected function useWorkflowGraph($xmlFilePath)
    {
        $this->transitions=[];
        $this->cycle=[];
        XmlGraph::setWorkflowGraph($this, $xmlFilePath);
        $this->setTransitionsAcls();
    }
}
