<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *  Control view Class Document
 *
 * @author Anakeen
 * @package FDL
 */
/**
 */
namespace Anakeen\SmartStructures\Cvdoc;

/**
 * Control view Class
 */
use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\SmartHooks;
use \SmartStructure\Attributes\Cvdoc as MyAttributes;

class CoreCVDoc extends \SmartStructure\Base
{
    /**
     * CVDoc has its own special access depend on special views
     * by default the three access are always set
     *
     * @var array
     */
    public $acls = array(
        "view",
        "edit",
        "delete"
    );
    
    public $nbId = 0;
    
    public $usefor = 'SW';
    public $defDoctype = 'P';
    public $attrPrefix = "CVI"; // prefix attribute
    
    /**
     * @var \Anakeen\Core\Internal\SmartElement instance document
     */
    public $doc = null;
    /**
     * @var CVDocHooks profil document
     */
    private $pdoc = null;
    // --------------------------------------------------------------------

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // first construct acl array
        if (isset($this->fromid)) {
            $this->defProfFamId = $this->fromid;
        } // it's a profil itself
        // don't use parent constructor because it could call this constructor => infinitive loop
        \Anakeen\Core\Internal\SmartElement::__construct($dbaccess, $id, $res, $dbid);
        
        $this->setAcls();
    }

    
    protected function postAffect(array $data, $more, $reset)
    {
        $this->setAcls();
    }
    public function setAcls()
    {
        $this->extendedAcls = array();
        $ti = $this->getMultipleRawValues("CV_IDVIEW");
        $tl = $this->getMultipleRawValues("CV_LVIEW");
        $tk = $this->getMultipleRawValues("CV_KVIEW");
        
        foreach ($tk as $k => $v) {
            if ($ti[$k] == "") {
                $cvk = "CV$k";
            } else {
                $cvk = $ti[$k];
            }
            $this->extendedAcls[$cvk] = array(
                "name" => $cvk,
                "description" => $tl[$k]
            );
            
            $this->acls[$cvk] = $cvk;
        }
    }
    
    public function computeCreationViewLabel($idCreationView)
    {
        if ('' !== $idCreationView) {
            $viewIds = $this->getAttributeValue(MyAttributes::cv_idview);
            $viewLabels = $this->getAttributeValue(MyAttributes::cv_lview);
            $viewIndex = array_search($idCreationView, $viewIds);
            if (false !== $viewIndex) {
                return sprintf("%s (%s)", $viewLabels[$viewIndex], $idCreationView);
            } else {
                return ' ';
            }
        } else {
            return ' ';
        }
    }
    
    public function isIdValid($value)
    {
        $err = "";
        $sug = array();
        $this->nbId++;

        $originals = DocumentAccess::$dacls;
        
        if (!preg_match('!^[0-9a-z_-]+$!i', $value)) {
            $err = sprintf(_("You must use only a-z, 0-9, _, - characters : \"%s\""), $value);
        } elseif (array_key_exists($value, $originals)) {
            $err = _("Impossible to name a view like a control acl");
        } else {
            $id_list = $this->getMultipleRawValues('CV_IDVIEW');
            $id_count = 0;
            foreach ($id_list as $id) {
                if ($id == $value) {
                    $id_count++;
                }
            }
            if ($id_count > 1) {
                $err = _("Impossible to have several identical names");
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    
    public function isLabelValid($value)
    {
        $err = '';
        $sug = array();
        if (strlen(trim($value)) == 0) {
            $err = _("Label must not be empty");
        }
        return array(
            'err' => $err,
            'sug' => $sug
        );
    }
    
    public function isCreationViewValid($idCreationView, $labelCreationView, $idViews)
    {
        $err = '';
        if ('' !== $idCreationView) {
            if (!is_array($idViews) || !in_array($idCreationView, $idViews)) {
                $err = sprintf(___("creation view '%s' does not exists", "CVDOC"), $labelCreationView);
            }
        }
        return $err;
    }
    /**
     * Return view properties
     * @param $vid
     * @return array|false false if vid not found
     */
    public function getView($vid)
    {
        $tv = $this->getArrayRawValues("cv_t_views");
        foreach ($tv as $v) {
            if ($v["cv_idview"] === $vid) {
                // found it
                foreach ($v as $k => $av) {
                    $v[strtoupper($k) ] = $av;
                }
                return $v;
            }
        }
        return false;
    }
    /**
     * @param string $vid view identifier
     * @return string the locale label
     */
    public function getLocaleViewLabel($vid)
    {
        $key = $this->getPropertyValue("name") . "#label#" . $vid;
        $lkey = _($key);
        if ($lkey != $key) {
            return $lkey;
        }
        $view = $this->getView($vid);
        return isset($view["CV_LVIEW"]) ? $view["CV_LVIEW"] : sprintf(_("Unlabeled view (%s)"), $vid);
    }
    /**
     * @param string $vid view identifier
     * @return string the locale menu label
     */
    public function getLocaleViewMenu($vid)
    {
        $key = $this->getPropertyValue("name") . "#menu#" . $vid;
        $lkey = _($key);
        if ($lkey != $key) {
            return $lkey;
        }
        $view = $this->getView($vid);
        return isset($view["CV_MENU"]) ? $view["CV_MENU"] : sprintf(_("Unlabeled menu (%s)"), $vid);
    }
    
    public function getViews()
    {
        $ti = $this->getMultipleRawValues("CV_IDVIEW");
        $tv = array();
        foreach ($ti as $k => $v) {
            $tv[$v] = $this->getView($v);
        }
        return $tv;
    }
    /**
     * get Views that can be displayed in a menu by example
     */
    public function getDisplayableViews()
    {
        $tv = $this->getArrayRawValues("cv_t_views");
        $cud = ($this->doc->CanEdit() == "");
        $displayableViews = array();
        foreach ($tv as $v) {
            $vid = $v[MyAttributes::cv_idview];
            $mode = $v[MyAttributes::cv_kview];
            if ($v[MyAttributes::cv_displayed] !== "no") {
                switch ($mode) {
                    case "VCONS":
                        if ($this->control($vid) == "") {
                            $displayableViews[] = $v;
                        }
                        break;

                    case "VEDIT":
                        if ($cud && $this->control($vid) == "") {
                            $displayableViews[] = $v;
                        }
                        break;
                }
            }
        }
        return $displayableViews;
    }

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $ti = $this->getMultipleRawValues("CV_IDVIEW");
            foreach ($ti as $k => $v) {
                if ($v == "") {
                    $ti[$k] = "CV$k";
                }
            }
            $this->setValue("CV_IDVIEW", $ti);
        })->addListener(SmartHooks::PREIMPORT, function () {
            return $this->verifyAllConstraints();
        });
    }

    public function docControl($aclname, $strict = false)
    {
        return \Anakeen\Core\Internal\SmartElement::control($aclname, $strict);
    }
    /**
     * Special control in case of dynamic controlled profil
     *
     * @param string $aclname
     * @param bool   $strict
     *
     * @return string
     */
    public function control($aclname, $strict = false)
    {
        $err = $this->docControl($aclname, $strict);
        if ($err == "") {
            return $err;
        } // normal case
        if ($this->getRawValue("DPDOC_FAMID") > 0) {
            if ($this->doc) {
                // special control for dynamic users
                if ($this->pdoc === null) {
                    $pdoc = createDoc($this->dbaccess, $this->fromid, false);
                    $pdoc->doctype = "T"; // temporary
                    //	$pdoc->setValue("DPDOC_FAMID",$this->getRawValue("DPDOC_FAMID"));
                    $err = $pdoc->add();
                    if ($err != "") {
                        return "CVDoc::Control:" . $err;
                    } // can't create profil
                    $pdoc->acls = $this->acls;
                    $pdoc->extendedAcls = $this->extendedAcls;
                    $pdoc->accessControl()->setProfil($this->profid, $this->doc);
                    
                    $this->pdoc = & $pdoc;
                }
                
                $err = $this->pdoc->docControl($aclname, $strict);
            }
        }
        return $err;
    }
    
    public function set(\Anakeen\Core\Internal\SmartElement &$doc)
    {
        if (!isset($this->doc)) {
            $this->doc = & $doc;
        }
    }
    /**
     * retrieve first compatible view
     * @param bool $edition if true edition view else consultation view
     * @return string[] view definition "cv_idview", "cv_mskid"
     */
    public function getPrimaryView($edition = false)
    {
        $view = '';
        if ($this->doc) {
            if ($edition && (!$this->doc->id)) {
                $vidcreate = $this->getRawValue("cv_idcview");
                if ($vidcreate) {
                    //	   control must be done by the caller
                    $viewU = $this->getView($vidcreate); // use it first if exist
                    $view = array();
                    foreach ($viewU as $k => $v) {
                        $view[strtolower($k) ] = $v;
                    }
                }
            }
            
            if (!$view) {
                $type = ($edition) ? "VEDIT" : "VCONS";
                // search preferred view
                $tv = $this->getArrayRawValues("cv_t_views");
                // sort
                usort($tv, "cmp_cvorder3");
                foreach ($tv as $k => $v) {
                    if ($v["cv_order"] > 0) {
                        if ($v["cv_kview"] == $type) {
                            $err = $this->control($v["cv_idview"]); // control special view
                            if ($err == "") {
                                $view = $v;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $view;
    }
}
