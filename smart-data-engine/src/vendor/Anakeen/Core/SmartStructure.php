<?php
/**
 * Family Document Class
 *
 *
 */

namespace Anakeen\Core;

use Anakeen\Core\Utils\Date;
use Anakeen\SmartHooks;

class SmartStructure extends \Anakeen\SmartStructures\Profiles\PFamHooks
{
    public $dbtable = "docfam";

    public $sqlcreate = "
create table docfam (cprofid int , 
                     dfldid int, 
                     cfldid int, 
                     ccvid int, 
                     cfallid int, 
                     ddocid int,
                     methods text,
                     defaultvalues jsonb,
                     schar char,
                     param jsonb,
                     genversion float,
                     maxrev int,
                     tagable text,
                     configuration text) inherits (doc);
create unique index idx_idfam on docfam(id);";
    public $sqltcreate = array();

    public $defDoctype = 'C';

    public $defaultview = "FDL:VIEWFAMCARD";

    public $attr;
    public $specialmenu = "FDL:POPUPFAMDETAIL";
    public $addfields
        = array(
            "dfldid",
            "cfldid",
            "ccvid",
            "cfallid",
            "cprofid",
            "ddocid",
            "methods",
            "defaultvalues",
            "param",
            "genversion",
            "schar",
            "maxrev",
            "tagable",
            "configuration"
        );
    public $genversion;
    public $dfldid;
    public $cfldid;
    public $ccvid;
    public $cfallid;
    public $cprofid;
    public $ddocid;
    public $methods;
    public $defaultvalues;
    public $param;
    public $schar;
    public $maxrev;
    public $configuration;
    public $tagable;
    private $_xtdefaultvalues; // dynamic used by ::getParams()
    private $_xtparam; // dynamic used by ::getDefValues()
    private $defaultSortProperties
        = array(
            'owner' => array(
                'sort' => 'no',
            ),
            'title' => array(
                'sort' => 'asc',
            ),
            'revision' => array(
                'sort' => 'no',
            ),
            'initid' => array(
                'sort' => 'desc',
            ),
            'mdate' => array(
                'sort' => 'desc',
            ),
            'state' => array(
                'sort' => 'asc',
            )
        );
    /**
     * @var bool bool(true) if object is "fully" instantiated using generated family class or bool(false) if object is instantiated
     * without generated class (e.g. when the family is imported and the generated class is not yet generated).
     */
    private $FINALCLASS_HasBeenLoaded = false;

    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0, $include = true)
    {
        foreach ($this->addfields as $f) {
            $this->fields[$f] = $f;
        }
        // specials characteristics R : revised on each modification
        parent::__construct($dbaccess, $id, $res, $dbid);
        if ($this->id && $this->doctype !== 'C') {
            throw new \Anakeen\Exception(sprintf("Cannot use this identifier \"%d\" as Smart Structure", $this->id));
        }

        if ($include && ($this->id > 0) && ($this->isAffected())) {
            $adoc = \Anakeen\Core\SEManager::getAttributesClassName($this->name);
            if (!\Anakeen\Core\Internal\Autoloader::findFile($adoc)) {
                // Workaround because autoload has eventually the class in its missing private key
                $attFileClass = \Anakeen\Core\SEManager::getAttributesClassFilename($this->name);
                if (file_exists($attFileClass)) {
                    /** @noinspection PhpIncludeInspection */
                    require_once($attFileClass);
                } else {
                    throw new \Anakeen\Exception(sprintf("Cannot access fields definition for %s (#%s) structure", $this->name, $this->id));
                }
            }
            $this->attributes = new $adoc();
            $this->attributes->orderAttributes();


            $this->FINALCLASS_HasBeenLoaded = true;
        }
    }

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            return \Anakeen\Core\SmartStructure\SmartStructureImport::refreshPhpPgDoc($this->dbaccess, $this->id);
        })->addListener(SmartHooks::POSTIMPORT, function () {
            return $this->updateWorkflowAttributes();
        })->addListener(SmartHooks::PREDELETE, function () {
            return ___("Structure cannot be deleted", "sde");
        })->addListener(SmartHooks::PRECREATED, function () {
            return $this->resetProperties();
        })->addListener(SmartHooks::POSTAFFECT, function () {
            $this->_xtdefaultvalues = null;
            $this->_xtparam = null;
        });
    }
    /**
     * return i18n title for family
     * based on name
     *
     * @return string
     */
    public function getCustomTitle()
    {
        $r = $this->name . '#title';
        $i = _($r);
        if ($i != $r) {
            return $i;
        }
        return $this->title;
    }

    public static function getLangTitle($values)
    {
        $r = $values["name"] . '#title';
        $i = _($r);
        if ($i != $r) {
            return $i;
        }
        return $values["title"];
    }


    protected function resetProperties()
    {
        $cdoc = $this->getFamilyDocument();
        if ($cdoc->isAlive()) {
            if (!$this->ccvid) {
                $this->ccvid = $cdoc->ccvid;
            }
            if (!$this->cprofid) {
                $this->cprofid = $cdoc->cprofid;
            }
            if (!$this->schar) {
                $this->schar = $cdoc->schar;
            }
            if (!$this->usefor) {
                $this->usefor = $cdoc->usefor;
            }
            if (!$this->tagable) {
                $this->tagable = $cdoc->tagable;
            }
        }
    }

    /**
     * update attributes of workflow if needed
     *
     * @return string
     */
    protected function updateWorkflowAttributes()
    {
        $err = '';
        if (strstr($this->usefor, 'W')) {
            $classw = $this->classname;
            if ($classw) {
                $w = new $classw();
                if ($w) {
                    if (is_a($w, \Anakeen\SmartStructures\Wdoc\WDocHooks::class)) {
                        /**
                         * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $w
                         */
                        $err = $w->createProfileAttribute($this->id);
                    }
                }
            } else {
                $err = "workflow need class";
            }
        }
        return $err;
    }



    //~~~~~~~~~~~~~~~~~~~~~~~~~ PARAMETERS ~~~~~~~~~~~~~~~~~~~~~~~~



    /**
     * return family parameter
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     *
     * @return string parameter value
     * @throws \Anakeen\Database\Exception
     */
    final public function getParameterRawValue($idp, $def = "")
    {
        $pValue = $this->getXValue("param", $idp);
        if ($pValue === '') {
            $defsys = $this->getDefValue($idp);
            if ($defsys !== '') {
                return $defsys;
            }
            return $def;
        }
        return $pValue;
    }

    /**
     * use in \Anakeen\Core\Internal\SmartElement::getParameterFamilyValue
     *
     * @param string $idp
     * @param string $def
     *
     * @see \Anakeen\Core\Internal\SmartElement::getParameterFamilyValue_
     * @return bool|string
     * @throws \Anakeen\Database\Exception
     */
    protected function getParameterFamilyRawValue($idp, $def)
    {
        return $this->getParameterRawValue($idp, $def);
    }

    /**
     * return all family parameter - seach in parents if parameter value is null
     *
     * @return array string parameter value
     * @throws \Anakeen\Database\Exception
     */
    public function getParams()
    {
        return $this->getXValues("param");
    }

    /**
     * return own family parameters values - no serach in parent families
     *
     * @return array string parameter value
     */
    public function getOwnParams()
    {
        return $this->explodeX($this->param);
    }

    /**
     * return the value of an list parameter document
     *
     * the parameter must be in an array or of a type '*list' like enumlist or textlist
     *
     * @param string $idAttr identifier of list parameter
     * @param string $def    default value returned if parameter not found or if is empty
     * @param int    $index  rank in case of multiple value
     *
     * @return array|string the list of parameter values
     * @throws \Anakeen\Database\Exception
     */
    public function getParamTValue($idAttr, $def = "", $index = -1)
    {
        $t = $this->rawValueToArray($this->getParameterRawValue("$idAttr", $def));
        if ($index == -1) {
            return $t;
        }
        if (isset($t[$index])) {
            return $t[$index];
        } else {
            return $def;
        }
    }

    /**
     * set family parameter value
     *
     * @param string $idp   parameter identifier
     * @param string $val   value of the parameter
     * @param bool   $check set to false when construct family
     *
     * @return string error message
     */
    public function setParam($idp, $val, $check = true)
    {
        $this->setChanged();
        $idp = strtolower($idp);

        $oa = null;
        if ($check) {
            $oa = $this->getAttribute($idp); // never use getAttribute if not check
            if (!$oa) {
                return \ErrorCode::getError('DOC0120', $idp, $this->getTitle(), $this->name);
            }
        }


        if (!empty($val) && $oa && ($oa->type == "date" || $oa->type == "timestamp")) {
            $err = $this->convertDateToiso($oa, $val);
            if ($err) {
                return $err;
            }
        }

        $err = '';
        if ($this->isComplete()) {
            $err = $this->checkSyntax($idp, $val);
        }
        if (!$err) {
            $this->setXValue("param", strtolower($idp), $val);
        }
        return $err;
    }

    private function convertDateToiso(\Anakeen\Core\SmartStructure\BasicAttribute $oa, &$val)
    {
        $localeconfig = \Anakeen\Core\ContextManager::getLocaleConfig();
        if ($localeconfig !== false) {
            if ($oa->type == "date" || $oa->type == "timestamp") {
                if ($oa->type == "date") {
                    $dateFormat = $localeconfig['dateFormat'];
                } else {
                    $dateFormat = $localeconfig['dateTimeFormat'];
                }

                if (is_array($val)) {
                    $tDates = $val;
                } else {
                    $tDates=[$val];
                }
                foreach ($tDates as $k => $date) {
                    $tDates[$k] = Date::stringDateToIso($date, $dateFormat);
                }
                if (is_array($val)) {
                    $val=$tDates;
                } else {
                    $val=$tDates[0];
                }
            } else {
                return sprintf(_("local config for date not found"));
            }
        }
        return '';
    }

    /**
     * Verify is family is under construction
     *
     * @return bool
     */
    private function isComplete()
    {
        return ($this->attributes && $this->attributes->attr);
    }

    /**
     * @param string $aid attribute identifier
     * @param string $val value to test
     *
     * @return string error message
     */
    private function checkSyntax($aid, $val)
    {
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
         */
        $oa = $this->getAttribute($aid);
        if (!$oa) {
            return '';
        } // cannot test in this case
        $err = '';
        $type = $oa->type;


        if (is_array($val)) {
            $vals = $val;
        } else {
            $vals[] = $val;
        }

        foreach ($vals as $ka => $av) {
            if (!self::seemsMethod($av)) {
                switch ($type) {
                    case 'money':
                    case 'double':
                        if (!empty($av) && (!is_numeric($av))) {
                            $err = sprintf(_("value [%s] is not a number"), $av);
                        }
                        break;

                    case 'int':
                        if (!empty($av)) {
                            if ((!is_numeric($av))) {
                                $err = sprintf(_("value [%s] is not a number"), $av);
                            }
                            if (!$err && (!ctype_digit($av))) {
                                $err = sprintf(_("value [%s] is not a integer"), $av);
                            }
                        }
                        break;
                }
                if (!$err) {
                    // verifiy constraint
                    if ($oa->phpconstraint) {
                        //print_r2($aid."[$ka]".$oa->phpconstraint);
                        $map[$aid] = $av;
                        $err = $this->applyMethod($oa->phpconstraint, null, $oa->isMultiple() ? $ka : -1, array(), $map);
                    }
                }
            }
        }
        return $err;
    }
    //~~~~~~~~~~~~~~~~~~~~~~~~~ DEFAULT VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~

    /**
     * return family default value
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     *
     * @return string default value
     * @throws \Anakeen\Database\Exception
     */
    public function getDefValue($idp, $def = "")
    {
        $x = $this->getXValue("defaultvalues", $idp, $def);

        return $x;
    }

    /**
     * return all family default values
     * search in parents families if value is null
     *
     * @return array string default value
     * @throws \Anakeen\Database\Exception
     */
    public function getDefValues()
    {
        return $this->getXValues("defaultvalues");
    }

    /**
     * return own default value not inherit default
     *
     * @return array string default value
     */
    public function getOwnDefValues()
    {
        return $this->explodeX($this->defaultvalues);
    }

    /**
     * set family default value
     *
     * @param string $idp parameter identifier
     * @param string $val value of the default
     * @param bool   $check
     *
     * @return string error message
     */
    public function setDefValue($idp, $val, $check = true)
    {
        $idp = strtolower($idp);
        $err = '';
        $oa = null;
        if ($check) {
            $oa = $this->getAttribute($idp);
            if (!$oa) {
                return \ErrorCode::getError('DOC0123', $idp, $this->getTitle(), $this->name);
            }
            if (is_array($val) && $oa->type !== "array" && $oa->getOption("multiple") !== "yes") {
                return \ErrorCode::getError('DOC0135', $idp, $this->getTitle(), $this->name);
            }
        }
        if (!empty($val) && $oa && ($oa->type == "date" || $oa->type == "timestamp")) {
            $err = $this->convertDateToiso($oa, $val);
        }
        $this->setXValue("defaultvalues", $idp, $val);
        return $err;
    }
    //~~~~~~~~~~~~~~~~~~~~~~~~~ X VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~

    /**
     * return family default value
     *
     * @param string $X   column name
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     *
     * @return string default value
     * @throws \Anakeen\Database\Exception
     */
    public function getXValue($X, $idp, $def = "")
    {
        $tval = "_xt$X";
        if (!isset($this->$tval)) {
            $this->getXValues($X);
        }

        $tval2 = $this->$tval;
        $v = isset($tval2[strtolower($idp)]) ? $tval2[strtolower($idp)] : '';
        if ($v == "-") {
            return $def;
        }
        if ($v !== "") {
            return $v;
        }
        return $def;
    }

    /**
     * explode param or defaultvalues string
     *
     * @param string $sx
     *
     * @return array
     */
    private function explodeX($sx)
    {
        $x = json_decode($sx, true);
        return empty($x) ? array() : $x;
    }

    /**
     * return all family default values
     *
     * @param string $X column name
     *
     * @return array string default value
     * @throws \Anakeen\Database\Exception
     */
    public function getXValues($X)
    {
        $Xval = "_xt$X";
        $defval = $this->$X;

        if ($this->$Xval) {
            return $this->$Xval;
        }

        $XS[$this->id] = $defval;
        $this->$Xval = array();
        $inhIds = array();
        if ($this->attributes !== null && isset($this->attributes->fromids) && is_array($this->attributes->fromids)) {
            $sql = sprintf("select id,%s from docfam where id in (%s)", pg_escape_string($X), implode(',', $this->attributes->fromids));
            DbManager::query($sql, $rx, false, false);
            foreach ($rx as $r) {
                $XS[$r["id"]] = $r[$X];
            }
            $inhIds = array_values($this->attributes->fromids);
        }
        if (!in_array($this->id, $inhIds)) {
            $inhIds[] = $this->id;
        }

        $txval = array();

        foreach ($inhIds as $famId) {
            $txvalh = $this->explodeX($XS[$famId]);
            foreach ($txvalh as $aid => $dval) {
                $txval[$aid] = ($dval == '-') ? null : $dval;
            }
        }
        if ($this->isComplete()) {
            uksort($txval, array(
                $this,
                "compareXOrder"
            ));
        }
        $this->$Xval = $txval;

        return $this->$Xval;
    }

    public function compareXOrder($a1, $a2)
    {
        $oa1 = $this->getAttribute($a1);
        $oa2 = $this->getAttribute($a2);
        if ($oa1 && $oa2) {
            if ($oa1->ordered > $oa2->ordered) {
                return 1;
            } elseif ($oa1->ordered < $oa2->ordered) {
                return -1;
            }
        }
        return 0;
    }

    /**
     * set family default value
     *
     * @param        $X
     * @param string $idp parameter identifier
     * @param string $val value of the default
     *
     * @return void
     */
    public function setXValue($X, $idp, $val)
    {
        $tval = "_xt$X";
        if (is_string($val) && json_decode($val)) {
            $val = json_decode($val);
        }

        $txval = $this->explodeX($this->$X);

        $txval[strtolower($idp)] = $val;
        $this->$tval = $txval;

        $tdefattr = array();
        foreach ($txval as $k => $v) {
            if ($k && ($v !== '')) {
                $tdefattr[$k] = $v;
            }
        }
        $this->$tval = null;
        $this->$X = json_encode($tdefattr);
    }

    final public function updateVaultIndex()
    {
        /*
         * Skip processing if the family has no attributes
         * This typically happens when the family is created for the first time at import and final class is not yet generated
        */
        if ((!$this->FINALCLASS_HasBeenLoaded) || (!isset($this->attributes->attr))) {
            return '';
        }

        $point = uniqid(__METHOD__);
        DbManager::savePoint($point);


        $dvi = new \DocVaultIndex($this->dbaccess);
        $dvi->deleteDoc($this->id);

        $tvid = \Anakeen\Core\Utils\VidExtractor::getVidsFromDocFam($this);

        foreach ($tvid as $k => $vid) {
            $dvi->docid = $this->id;
            $dvi->vaultid = $vid;
            $dvi->add();
        }
        DbManager::commitPoint($point);

        return '';
    }

    public function saveVaultFile($vid, $stream)
    {
        $err = '';
        if (is_resource($stream) && get_resource_type($stream) == "stream") {
            $ext = "nop";
            $filename = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/_fdl") . ".$ext";
            $tmpstream = fopen($filename, "w");
            while (!feof($stream)) {
                if (false === fwrite($tmpstream, fread($stream, 4096))) {
                    $err = "403 Forbidden";
                    break;
                }
            }
            fclose($tmpstream);
            if (!$err) {
                $vf = new \Anakeen\Vault\VaultFile();
                $info = null;
                $err = $vf->Retrieve($vid, $info);
                if ($err == "") {
                    $err = $vf->Save($filename, false, $vid);
                }
                unlink($filename);
            }
            return $err;
        }
        return '';
    }


    /**
     * @param bool $linkInclude if false fdl.xsd is write inside else use an include directive
     *
     * @return string
     */
    public function getXmlSchema($linkInclude = false)
    {
        $lay = new \Anakeen\Layout\TextLayout(sprintf("%s/vendor/Anakeen/Core/Layout/family_schema.xml", DEFAULT_PUBDIR));
        $lay->set("famname", strtolower($this->name));
        $lay->set("famtitle", strtolower($this->getTitle()));
        $lay->set("include", $linkInclude);
        if ($linkInclude) {
            $lay->set("includefdlxsd", "");
        } else {
            $xsd = new \DOMDocument();
            $xsd->load(sprintf("%s/vendor/Anakeen/Core/Layout/fdl.xsd", DEFAULT_PUBDIR));
            $xsd->preserveWhiteSpace = false;
            $xsd->formatOutput = true;
            $innerXml = '';
            $rootNode = $xsd->documentElement;
            /**
             * @var \DOMNode $node
             */
            foreach ($rootNode->childNodes as $subnode) {
                $innerXml .= ($xsd->saveXML($subnode));
            }

            $lay->set("includefdlxsd", $innerXml);
        }

        $level1 = array();
        $la = $this->getAttributes();
        $tax = array();

        foreach ($la as $k => $v) {
            if ((!$v) || ($v->getOption("autotitle") == "yes") || ($v->usefor == 'Q')) {
                unset($la[$k]);
            }
        }
        foreach ($la as $k => $v) {
            if (($v->id != \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) &&
                ($v->type == 'frame' || $v->type == "tab") &&
                ((!$v->fieldSet) || $v->fieldSet->id == \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD)) {
                $level1[] = array(
                    "level1name" => $k
                );
                $tax[] = array(
                    "tax" => $v->getXmlSchema($la)
                );
            }
        };

        $lay->setBlockData("ATTR", $tax);
        $lay->setBlockData("LEVEL1", $level1);

        $xsd = new \DOMDocument();
        $xsd->preserveWhiteSpace = false;
        $xsd->formatOutput = true;
        $xsd->loadXML($lay->gen());

        return ($xsd->saveXML());
    }
    /*
        private function loadDefaultSortProperties() {
        $confStore = new ConfigurationStore();
        foreach ($this->defaultSortProperties as $propName => $pValues) {
            foreach ($pValues as $pName => $pValue) {
                $confStore->add('sortProperties', $propName, $pName, $pValue);
            }
        }
        $conf = $confStore->getText();
        if ($conf === false) {
            return false;
        }
        $this->configuration = $conf;
        error_log(__METHOD__." ".sprintf("conf = [%s]", $conf));
        return $this;
        }
    */
    /**
     * Reset properties configuration
     *
     * @return \Anakeen\Core\SmartStructure
     */
    public function resetPropertiesParameters()
    {
        $this->configuration = '';
        return $this;
    }

    /**
     * Get a property's parameter's value
     *
     * @param string $propName The property's name
     * @param string $pName    The parameter's name
     *
     * @return bool|string boolean false on error, string containing the parameter's value
     */
    public function getPropertyParameter($propName, $pName)
    {
        $propName = strtolower($propName);

        $confStore = new \ConfigurationStore();
        if ($confStore->load($this->configuration) === false) {
            return false;
        }

        $class = \CheckProp::getParameterClassMap($pName);
        /**
         * @var string $pValue
         */
        $pValue = $confStore->get($class, $propName, $pName);

        return $pValue;
    }

    /**
     * Set a parameter's value on a property
     *
     * Note: The value is set on the object but not saved in the
     * database, so it's your responsibility to call modify() if you
     * want to make the change persistent.
     *
     * @param string $propName The property's name
     * @param string $pName    The parameter's name
     * @param string $pValue   The parameter's value
     *
     * @return bool boolean false on error, or boolean true on success
     */
    public function setPropertyParameter($propName, $pName, $pValue)
    {
        $propName = strtolower($propName);

        $confStore = new \ConfigurationStore();
        if ($confStore->load($this->configuration) === false) {
            return false;
        }

        $class = \CheckProp::getParameterClassMap($pName);
        $confStore->add($class, $propName, $pName, $pValue);

        $conf = $confStore->getText();
        if ($conf === false) {
            return false;
        }

        $this->configuration = $conf;
        return true;
    }

    /**
     * Get sortable properties.
     *
     * @return array properties' Names with their set of parameters
     */
    public function getSortProperties()
    {
        $res = array();
        /*
         * Lookup default parameters
        */
        foreach ($this->defaultSortProperties as $propName => $params) {
            if (isset($params['sort']) && $params['sort'] != 'no') {
                $res[$propName] = $params;
            }
        }
        $confStore = new \ConfigurationStore();
        if ($confStore->load($this->configuration) === false) {
            return $res;
        }
        /*
         * Lookup custom parameters
        */
        $props = $confStore->get('sortProperties', null, 'sort');
        if ($props === null) {
            return $res;
        }
        foreach ($props as $propName => $params) {
            if (isset($params['sort']) && $params['sort'] != 'no') {
                $res[$propName] = $params;
            }
        }

        return $res;
    }

    public function postUpdate()
    {
        if (($err = $this->updateVaultIndex()) !== '') {
            return $err;
        }
        return parent::PostUpdate();
    }
}
