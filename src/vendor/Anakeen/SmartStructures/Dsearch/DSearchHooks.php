<?php
/**
 * Detailled search
 */

namespace Anakeen\SmartStructures\Dsearch;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\DocManager;
use Anakeen\SmartStructures\Dir\DirLib;
use \Dcp\Core\Exception;

class DSearchHooks extends \SmartStructure\Search
{
    /**
     * /**
     * Last suggestion for constraints
     *
     * @var string
     */
    private $last_sug;

    public $defaultedit = "FREEDOM:EDITDSEARCH"; #N_("include") N_("equal") N_("equal") _("not equal") N_("is empty") N_("is not empty") N_("one value equal")
    public $defaultview = "FREEDOM:VIEWDSEARCH"; #N_("not include") N_("begin by") N_("not equal") N_("&gt; or equal") N_("&lt; or equal")  N_("content file word") N_("content file expression")

    /**
     * @var \Anakeen\Core\SmartStructure |null
     */
    protected $searchfam = null;

    /**
     * return sql query to search wanted document
     *
     * @param string $keyword
     * @param int    $famid
     * @param string $latest
     * @param bool   $sensitive
     * @param int    $dirid
     * @param bool   $subfolder
     * @param bool   $full
     *
     * @return array|bool|string
     * @throws Exception
     * @throws \Dcp\Db\Exception
     * @throws \Exception
     */
    public function ComputeQuery($keyword = "", $famid = -1, $latest = "yes", $sensitive = false, $dirid = -1, $subfolder = true, $full = false)
    {
        if ($dirid > 0) {
            if ($subfolder) {
                $cdirid = DirLib::getRChildDirId($this->dbaccess, $dirid);
            } else {
                $cdirid = $dirid;
            }
        } else {
            $cdirid = 0;
        };

        $filters = $this->getSqlGeneralFilters($keyword, $latest, $sensitive);
        $cond = $this->getSqlDetailFilter();
        if ($cond === false) {
            return array(
                false
            );
        }
        $distinct = false;
        $only = '';
        if ($latest == "lastfixed") {
            $distinct = true;
        }
        if ($cond != "") {
            $filters[] = $cond;
        }
        if ($this->getRawValue("se_famonly") == "yes") {
            if (!is_numeric($famid)) {
                $famid = DocManager::getFamilyIdFromName($famid);
            }
            $only = "only";
        }
        $query = DirLib::getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters, $distinct, $latest == "yes", $this->getRawValue("se_trash"), false, $level = 2, $join = '', $only);

        return $query;
    }

    /**
     * Change queries when use filters objects instead of declarative criteria
     */
    public function getQuery()
    {
        $filtersType = $this->getMultipleRawValues("se_typefilter");
        if ((count($this->getMultipleRawValues("se_filter")) > 0) && (empty($filtersType[0]) || $filtersType[0] != "generated")) {
            $queries = array();
            $filters = $this->getMultipleRawValues("se_filter");
            foreach ($filters as $filter) {
                $q = $this->getSqlXmlFilter($filter);
                if ($q) {
                    $queries[] = $q;
                }
            }
            return $queries;
        } else {
            return parent::getQuery();
        }
    }

    public function postStore()
    {
        $err = parent::postStore();
        try {
            $this->getSqlDetailFilter(true);
        } catch (\Exception $e) {
            $err .= $e->getMessage();
        }
        $err .= $this->updateFromXmlFilter();
        $err .= $this->updateXmlFilter();
        if ((!$err) && ($this->isChanged())) {
            $err = $this->modify();
        }
        return $err;
    }



    /**
     * update somes attributes from Xml filter
     *
     * @return string error message
     */
    public function updateFromXmlFilter()
    {
        // update only if one filter
        $err = '';
        if (count($this->getMultipleRawValues("se_filter")) == 1) {
            // try to update se_famid
            $filters = $this->getMultipleRawValues("se_filter");
            $filtersType = $this->getMultipleRawValues("se_typefilter");
            $filter = $filters[0];
            $filterType = $filtersType[0];
            if ($filterType != "generated") {
                $famid = '';
                $root = simplexml_load_string($filter);
                $std = $this->simpleXml2StdClass($root);
                if ($std->family) {
                    if (!is_numeric($std->family)) {
                        if (preg_match("/([\w:]*)\s?(strict)?/", trim($std->family), $reg)) {
                            if (!is_numeric($reg[1])) {
                                $reg[1] = DocManager::getFamilyIdFromName($reg[1]);
                            }
                            if ($reg[2] == "strict") {
                                $famid = '-' . $reg[1];
                            } else {
                                $famid = $reg[1];
                            }
                        }
                    } else {
                        $famid = ($std->family);
                    }
                    if ($famid) {
                        $err = $this->setValue("se_famid", abs($famid));
                        $err .= $this->setValue("se_famonly", ($famid > 0) ? "no" : "yes");
                    }
                }
            }
        }
        return $err;
    }

    /**
     * update somes attributes from Xml filter
     *
     * @return string error message
     */
    public function updateXmlFilter()
    {
        // update only if one filter
        $err = '';
        if (count($this->getMultipleRawValues("se_filter")) < 2) {
            // try to update se_famid
            $typeFilters = $this->getMultipleRawValues("se_typefilter");
            if (count($this->getMultipleRawValues("se_filter")) == 1) {
                if ($typeFilters[0] != "generated") {
                    return '';
                } // don't update specified filter created by data API
            }
            if ($this->getRawValue("se_famid")) {
                $filterXml = sprintf("<filter><family>%s%s</family>", $this->getRawValue("se_famid"), ($this->getRawValue("se_famonly") == "yes" ? " strict" : ""));

                $filterXml .= "</filter>";
                $this->setValue("se_typefilter", "generated"); // only one
                $this->setValue("se_filter", $filterXml);
            }
        }
        return $err;
    }

    /**
     * return a query from on filter object
     *
     * @param string $xml xml filter object
     *
     * @return string the query
     */
    public function getSqlXmlFilter($xml)
    {
        $root = simplexml_load_string($xml);
        // trasnform XmlObject to StdClass object
        $std = $this->simpleXml2StdClass($root);
        $famid = $sql = "";
        $this->object2SqlFilter($std, $famid, $sql);

        $filters[] = $sql;
        $cdirid = 0;
        $q = DirLib::getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters);
        if (count($q) == 1) {
            $q0 = $q[0]; // need a tempo variable : don't know why
            return ($q0);
        }

        return false;
    }

    /**
     * cast SimpleXMLElment to stdClass
     *
     * @param \SimpleXMLElement $xml
     *
     * @return \stdClass return  object or value if it is a leaf
     */
    public function simpleXml2StdClass(\SimpleXMLElement $xml)
    {
        $std = null;
        if ($xml->count() == 0) {
            /** @var array $xml */
            return current($xml);
        } else {
            foreach ($xml as $k => $se) {
                if (isset($std->$k)) {
                    if (!is_array($std->$k)) {
                        $std->$k = array(
                            $std->$k
                        );
                    }
                    array_push($std->$k, $this->simpleXml2StdClass($se));
                } else {
                    if ($std === null) {
                        $std = new \stdClass();
                    }
                    $std->$k = $this->simpleXml2StdClass($se);
                }
            }
        }
        return $std;
    }

    public function preConsultation()
    {
        $err = parent::preConsultation();
        if ($err !== '') {
            return $err;
        }
        if (count($this->getMultipleRawValues("se_filter")) > 0) {
            if ($this->defaultview == "FREEDOM:VIEWDSEARCH") {
                $type = $this->getMultipleRawValues("se_typefilter");
                if ($type[0] != "generated") {
                    $this->defaultview = "FDL:VIEWBODYCARD";
                }
            }
        }
        return '';
    }

    public function preEdition()
    {
        if (count($this->getMultipleRawValues("se_filter")) > 0) {
            $type = $this->getMultipleRawValues("se_typefilter");
            if ($type[0] != "generated") {
                $this->defaultedit = "FDL:EDITBODYCARD";
                /**
                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
                 */
                $this->getAttribute('se_t_detail', $oa);
                $oa->setVisibility('R');
                $this->getAttribute('se_t_filters', $oa);
                $oa->setVisibility('W');

                $this->getAttribute('se_filter', $oa);
                $oa->setVisibility('W');
            }
        }
    }

    /**
     * return error if query filters are not compatibles
     * verify parenthesis
     *
     * @return string error message , empty if no errors
     */
    public function getSqlParseError()
    {
        $err = "";
        $tlp = $this->getMultipleRawValues("SE_LEFTP");
        $tlr = $this->getMultipleRawValues("SE_RIGHTP");
        $clp = 0;
        $clr = 0;
        //if (count($tlp) > count($tlr)) $err=sprintf(_("left parenthesis is not closed"));
        if ($err == "") {
            foreach ($tlp as $lp) {
                if ($lp == "yes") {
                    $clp++;
                }
            }
            foreach ($tlr as $lr) {
                if ($lr == "yes") {
                    $clr++;
                }
            }
            if ($clp != $clr) {
                $err = sprintf(_("parenthesis number mismatch : %d left, %d right"), $clp, $clr);
            }
        }
        return $err;
    }

    /**
     * Check the given string is a valid timestamp (or date)
     *
     * @param $str
     *
     * @return string empty string if valid or error message
     */
    private function isValidTimestamp($str)
    {
        $this->last_sug = '';
        /* Check french format */
        if (preg_match('|^\d\d/\d\d/\d\d\d\d(\s+\d\d:\d\d(:\d\d)?)?$|', $str)) {
            return '';
        }
        /* Check ISO format */
        if (preg_match('@^\d\d\d\d-\d\d-\d\d([\s+|T]\d\d:\d\d(:\d\d)?)?$@', $str)) {
            return '';
        }
        $this->last_sug = $this->getDate(0, '', '', true);
        return _("DetailSearch:malformed timestamp") . ": $str";
    }

    /**
     * Check the given string is a valid Postgresql's RE
     *
     * @param string $str
     *
     * @return string empty string if valid or error message
     * @throws Exception
     */
    private function isValidPgRegex($str)
    {
        $err = '';
        $this->last_sug = '';
        $point = "dcp:isValidPgRegex";
        DbManager::savePoint($point);
        $q = sprintf("SELECT regexp_matches('', E'%s')", pg_escape_string($str));
        try {
            DbManager::query($q, $res);
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
        DbManager::rollbackPoint($point);
        if ($err != '') {
            $err = _("invalid regular expression");
            $this->last_sug = preg_quote($str, '');
        }
        return $err;
    }

    /**
     * Check validity of a condition tuple (attr, op, value)
     *
     * @param string $attr  The attribute for the condition
     * @param string $op    The operator for the condition
     * @param string $value The value for the condition
     *
     * @return array|string empty string if valid or error message
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function isValidCondition($attr, $op, $value)
    {
        /* Accept method name */
        if ($value !== '' && $this->getMethodName($value) !== '') {
            return array(
                'err' => '',
                'sug' => ''
            );
        }
        /* Accept parameter */
        if (substr($value, 0, 1) == '?') {
            return array(
                'err' => '',
                'sug' => ''
            );
        }
        /* Call getSqlCond() in validation mode (validateCond = true) */
        $err = '';
        $this->getSqlCond($attr, $op, $value, '', $err, true);
        if ($err != '') {
            $err = sprintf(_("Invalid condition for attribute '%s' with value '%s': %s"), $attr, $value, $err);
        }
        return array(
            'err' => $err,
            'sug' => isset($this->last_sug) ? $this->last_sug : ''
        );
    }

    /**
     * Check for properly balanced conditions' parenthesis
     */
    private function checkConditionsParens()
    {
        $err = '';
        $lp = $this->getMultipleRawValues('se_leftp');
        $rp = $this->getMultipleRawValues('se_rightp');
        $pc = 0;
        foreach ($lp as $p) {
            if ($p == 'yes') {
                $pc++;
            }
        }
        foreach ($rp as $p) {
            if ($p == 'yes') {
                $pc--;
            }
        }
        if ($pc != 0) {
            $err = _("DetailSearch:unbalanced parenthesis");
        }
        return $err;
    }

    /**
     * Check global coherence of conditions
     */
    public function checkConditions()
    {
        $err = '';
        $err .= $this->checkConditionsParens();
        return array(
            'err' => $err,
            'sug' => ''
        );
    }

    /**
     * return sql part from operator
     *
     * @param string $col  a column : property or attribute name
     * @param string $op   one of this ::top keys : =, !=, >, ....
     * @param string $val  value use for test
     * @param string $val2 second value use for test with >< operator
     * @param string $err
     * @param bool   $validateCond
     *
     * @return string the sql query part
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function getSqlCond($col, $op, $val = "", $val2 = "", &$err = "", $validateCond = false)
    {
        if ((!$this->searchfam) || ($this->searchfam->id != $this->getRawValue("se_famid"))) {
            $this->searchfam = DocManager::getFamily($this->getRawValue("se_famid"));
        }
        $col = trim(strtok($col, ' ')); // a col is one word only (prevent injection)
        // because for historic reason revdate is not a date type
        if (($col == "revdate") && ($val != '') && (!is_numeric($val))) {
            $val = stringdatetounixts($val);
        }
        $stateCol = '';
        if ($col == "activity" || $col == "fixstate") {
            $stateCol = $col;
            $col = "state";
        }
        $atype = '';
        $oa = null;
        if ($this->searchfam) {
            $oa = $this->searchfam->getAttribute($col);
        }
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
         */
        if ($oa) {
            $atype = $oa->type;
        } elseif (!empty(\Doc::$infofields[$col])) {
            $atype = \Doc::$infofields[$col]["type"];
        }
        if (($atype == "date" || $atype == "timestamp")) {
            if ($col == 'revdate') {
                if ($op == "=") {
                    $val2 = $val + 85399; // tonight
                    $op = "><";
                }
            } else {
                $hms = '';
                if (($atype == "timestamp")) {
                    $pos = strpos($val, ' ');
                    if ($pos != false) {
                        $hms = substr($val, $pos + 1);
                    }
                }

                $cfgdate = ContextManager::getLocaleConfig();
                if ($val) {
                    $val = stringDateToIso($val, $cfgdate['dateFormat']);
                }
                if ($val2) {
                    $val2 = stringDateToIso($val2, $cfgdate['dateFormat']);
                }

                if (($atype == "timestamp") && ($op == "=")) {
                    $val = trim($val);
                    if (strlen($val) == 10) {
                        if ($hms == '') {
                            $val2 = $val . " 23:59:59";
                            $val .= " 00:00:00";
                            $op = "><";
                        } elseif (strlen($hms) == 2) {
                            $val2 = $val . ' ' . $hms . ":59:59";
                            $val .= ' ' . $hms . ":00:00";
                            $op = "><";
                        } elseif (strlen($hms) == 5) {
                            $val2 = $val . ' ' . $hms . ":59";
                            $val .= ' ' . $hms . ":00";
                            $op = "><";
                        } else {
                            $val .= ' ' . $hms;
                        }
                    }
                }

                if ($validateCond
                    && in_array($op, array(
                        "=",
                        "!=",
                        ">",
                        "<",
                        ">=",
                        "<=",
                        "~y"
                    ))) {
                    if (($err = $this->isValidTimestamp($val)) != '') {
                        return '';
                    }
                }
            }
        }
        $cond = '';
        switch ($op) {
            case "is null":
                switch ($atype) {
                    case "int":
                    case "uid":
                    case "double":
                    case "money":
                        $cond = sprintf(" (%s is null or %s = 0) ", $col, $col);
                        break;

                    case "date":
                    case "time":
                        $cond = sprintf(" (%s is null) ", $col);
                        break;

                    default:
                        $cond = sprintf(" (%s is null or %s = '') ", $col, $col);
                }

                break;

            case "is not null":
                $cond = " " . $col . " " . trim($op) . " ";
                break;

            case "~*":
                if ($validateCond) {
                    if (($err = $this->isValidPgRegex($val)) != '') {
                        return '';
                    }
                }
                if (trim($val) != "") {
                    $cond = " " . $col . " " . trim($op) . " " . $this->_pg_val($val) . " ";
                }
                break;

            case "~^":
                if ($validateCond) {
                    if (($err = $this->isValidPgRegex($val)) != '') {
                        return '';
                    }
                }
                if (trim($val) != "") {
                    $cond = " " . $col . "~* '^" . pg_escape_string(trim($val)) . "' ";
                }
                break;

            case "~y":
                if (!is_array($val)) {
                    $val = $this->rawValueToArray($val);
                }
                foreach ($val as & $v) {
                    $v = self::pgRegexpQuote($v);
                }
                unset($v);
                if (count($val) > 0) {
                    $cond = " " . $col . " ~ E'\\\\y(" . pg_escape_string(implode('|', $val)) . ")\\\\y' ";
                }
                break;

            case "><":
                if ((trim($val) != "") && (trim($val2) != "")) {
                    $cond = sprintf("%s >= %s and %s <= %s", $col, $this->_pg_val($val), $col, $this->_pg_val($val2));
                }
                break;

            case "=~*":
                switch ($atype) {
                    case "uid":
                        if ($validateCond) {
                            if (($err = $this->isValidPgRegex($val)) != '') {
                                return '';
                            }
                        }
                        DbManager::query(sprintf("select id from users where firstname ~* '%s' or lastname ~* '%s'", pg_escape_string($val), pg_escape_string($val)), $ids, true);

                        if (count($ids) == 0) {
                            $cond = "false";
                        } elseif (count($ids) == 1) {
                            $cond = " " . $col . " = " . intval($ids[0]) . " ";
                        } else {
                            $cond = " " . $col . " in (" . implode(',', $ids) . ") ";
                        }

                        break;

                    case "account":
                    case "docid":
                        if ($validateCond) {
                            if (($err = $this->isValidPgRegex($val)) != '') {
                                return '';
                            }
                        }
                        if ($oa) {
                            $otitle = $oa->getOption("doctitle");
                            if (!$otitle) {
                                $fid = $oa->format;
                                if (!$fid && $oa->type == "account") {
                                    $fid = "IUSER";
                                }
                                if (!$fid) {
                                    $err = sprintf(_("no compatible type with operator %s"), $op);
                                } else {
                                    if (!is_numeric($fid)) {
                                        $fid = DocManager::getFamilyIdFromName($fid);
                                    }
                                    DbManager::query(sprintf("select id from doc%d where title ~* '%s'", $fid, pg_escape_string($val)), $ids, true);

                                    if (count($ids) == 0) {
                                        $cond = "false";
                                    } elseif (count($ids) == 1) {
                                        $cond = " " . $col . " = '" . intval($ids[0]) . "' ";
                                    } else {
                                        $cond = " " . $col . " in ('" . implode("','", $ids) . "') ";
                                    }
                                }
                            } else {
                                if ($otitle == "auto") {
                                    $otitle = $oa->id . "_title";
                                }
                                $oat = $this->searchfam->getAttribute($otitle);
                                if ($oat) {
                                    $cond = " " . $oat->id . " ~* '" . pg_escape_string(trim($val)) . "' ";
                                } else {
                                    $err = sprintf(_("attribute %s : cannot detect title attribute"), $col);
                                }
                            }
                        } elseif ($col == "fromid") {
                            DbManager::query(sprintf("select id from docfam where title ~* '%s'", pg_escape_string($val)), $ids, true);

                            if (count($ids) == 0) {
                                $cond = "false";
                            } elseif (count($ids) == 1) {
                                $cond = " " . $col . " = " . intval($ids[0]) . " ";
                            } else {
                                $cond = " " . $col . " in (" . implode(",", $ids) . ") ";
                            }
                        }
                        break;

                    default:
                        if ($atype) {
                            $err = sprintf(_("attribute %s : %s type is not allowed with %s operator"), $col, $atype, $op);
                        } else {
                            $err = sprintf(_("attribute %s not found [%s]"), $col, $atype);
                        }
                }
                break;

            case "~@":
                if ($validateCond) {
                    if (($err = $this->isValidPgRegex($val)) != '') {
                        return '';
                    }
                }
                if (trim($val) != "") {
                    $cond = " " . $col . '_txt' . " ~ '" . strtolower($val) . "' ";
                }
                break;

            case "=@":
            case "@@":
                if (trim($val) != "") {
                    $tstatickeys = explode(' ', $val);
                    if (count($tstatickeys) > 1) {
                        $keyword = str_replace(" ", "&", trim($val));
                    } else {
                        $keyword = trim($val);
                    }
                    if ($op == "@@") {
                        $cond = " " . $col . '_vec' . " @@ to_tsquery('french','." . pg_escape_string(\Anakeen\Core\Utils\Strings::Unaccent(strtolower($keyword))) . "') ";
                    } elseif ($op == "=@") {
                        $cond = sprintf("fulltext @@ to_tsquery('french','%s') ", pg_escape_string(\Anakeen\Core\Utils\Strings::Unaccent(strtolower($keyword))));
                    }
                }
                break;

            default:
                switch ($atype) {
                    case "enum":
                        $enum = $oa->getEnum();
                        if (strrpos($val, '.') !== false) {
                            $val = substr($val, strrpos($val, '.') + 1);
                        }
                        $tkids = array();
                        foreach ($enum as $k => $v) {
                            if (in_array($val, explode(".", $k))) {
                                $tkids[] = substr($k, strrpos("." . $k, '.'));
                            }
                        }

                        if ($op == '=') {
                            if ($oa->repeat) {
                                $cond = " " . $col . " ~ E'\\\\y(" . pg_escape_string(implode('|', $tkids)) . ")\\\\y' ";
                            } else {
                                $cond = " $col='" . implode("' or $col='", $tkids) . "'";
                            }
                        } elseif ($op == '!=') {
                            if ($oa->repeat) {
                                $cond1 = " " . $col . " !~ E'\\\\y(" . pg_escape_string(implode('|', $tkids)) . ")\\\\y' ";
                            } else {
                                $cond1 = " $col !='" . implode("' and $col != '", $tkids) . "'";
                            }
                            $cond = " (($cond1) or ($col is null))";
                        } elseif ($op == '!~*') {
                            if ($validateCond) {
                                if (($err = $this->isValidPgRegex($val)) != '') {
                                    return '';
                                }
                            }
                            $cond = sprintf("( (%s is null) or (%s %s %s) )", $col, $col, trim($op), $this->_pg_val($val));
                        }

                        break;

                    default:
                        if ($atype == "docid") {
                            if (!is_numeric($val)) {
                                $val = DocManager::getIdFromName($val);
                            }
                        }
                        $cond1 = " " . $col . " " . trim($op) . $this->_pg_val($val) . " ";
                        if (($op == '!=') || ($op == '!~*')) {
                            if ($validateCond && $op == '!~*') {
                                if (($err = $this->isValidPgRegex($val)) != '') {
                                    return '';
                                }
                            }
                            $cond = "(($cond1) or ($col is null))";
                        } else {
                            $cond = $cond1;
                        }
                }
        }
        if (!$cond) {
            $cond = "true";
        } elseif ($stateCol == "activity") {
            $cond = sprintf("(%s and locked != -1)", $cond);
        } elseif ($stateCol == "fixstate") {
            $cond = sprintf("(%s and locked = -1)", $cond);
        }
        return $cond;
    }

    private static function _pg_val($s)
    {
        if (substr($s, 0, 2) == ':@') {
            return " " . trim(strtok(substr($s, 2), " \t")) . " ";
        } else {
            return " '" . pg_escape_string(trim($s)) . "' ";
        }
    }

    /**
     * return array of sql filter needed to search wanted document
     *
     * @param bool $validateCond
     *
     * @return string
     * @throws Exception
     * @throws \Dcp\Db\Exception
     * @throws \Exception
     */
    public function getSqlDetailFilter($validateCond = false)
    {
        $ol = $this->getRawValue("SE_OL");
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $tf = $this->getMultipleRawValues("SE_FUNCS");
        $tlp = $this->getMultipleRawValues("SE_LEFTP");
        $tlr = $this->getMultipleRawValues("SE_RIGHTP");
        $tols = $this->getMultipleRawValues("SE_OLS");

        if ($ol == "") {
            // try in old version
            $ols = $this->getMultipleRawValues("SE_OLS");
            $ol = isset($ols[1]) ? $ols[1] : '';
            if ($ol) {
                $this->setValue("SE_OL", $ol);
                $this->modify();
            }
        }
        if ($ol == "") {
            $ol = "and";
        }
        $cond = "";
        if (!$this->searchfam) {
            $this->searchfam = DocManager::getFamily($this->getRawValue("se_famid"));
        }
        if ((count($taid) > 1) || (count($taid) > 0 && $taid[0] != "")) {
            // special loop for revdate
            foreach ($tkey as $k => $v) {
                // Does it looks like a method name?
                $methodName = $this->getMethodName($v);
                if ($methodName != '') {
                    // it's method call
                    $workdoc = $this->getSearchFamilyDocument();
                    if (!$workdoc) {
                        $workdoc = $this;
                    }
                    if (!$workdoc->isValidSearchMethod($workdoc, $methodName)) {
                        return 'false';
                    }
                    $rv = $workdoc->ApplyMethod($v);
                    $tkey[$k] = $rv;
                }
                if (substr($v, 0, 1) == "?") {
                    // it's a parameter
                    $rv = getHttpVars(substr($v, 1), "-");
                    if ($rv == "-") {
                        return (false);
                    }
                    if ($rv === "" || $rv === " ") {
                        unset($taid[$k]);
                    } else {
                        $tkey[$k] = $rv;
                    }
                }
                if ($taid[$k] == "revdate") {
                    if (substr_count($tkey[$k], '/') === 2) {
                        list($dd, $mm, $yyyy) = explode("/", $tkey[$k]);
                        if ($yyyy > 0) {
                            $tkey[$k] = mktime(0, 0, 0, $mm, $dd, $yyyy);
                        }
                    }
                }
            }
            foreach ($taid as $k => $v) {
                $cond1 = $this->getSqlCond($taid[$k], trim($tf[$k]), $tkey[$k], "", $err, $validateCond);
                if ($validateCond && $err != '') {
                    throw new \Exception($err);
                }
                if ($cond == "") {
                    if (isset($tlp[$k]) && $tlp[$k] == "yes") {
                        $cond = '(' . $cond1 . " ";
                    } else {
                        $cond = $cond1 . " ";
                    }
                    if (isset($tlr[$k]) && $tlr[$k] == "yes") {
                        $cond .= ')';
                    }
                } elseif ($cond1 != "") {
                    if (isset($tols[$k]) && $tols[$k] != "" && $ol === "perso") {
                        $ol1 = $tols[$k];
                    } else {
                        $ol1 = $ol;
                    }

                    if ($ol1 === "perso") {
                        // workaround if user set global as condition
                        $ol1 = "and";
                    }
                    if (isset($tlp[$k]) && $tlp[$k] == "yes") {
                        $cond .= $ol1 . ' (' . $cond1 . " ";
                    } else {
                        $cond .= $ol1 . " " . $cond1 . " ";
                    }
                    if (isset($tlr[$k]) && $tlr[$k] == "yes") {
                        $cond .= ') ';
                    }
                }
            }
        }
        if (trim($cond) == "") {
            $cond = "true";
        }
        return $cond;
    }

    /**
     * return true if the search has parameters
     */
    public function isParameterizable()
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        if (empty($tkey)) {
            return false;
        }
        if ((count($tkey) > 1) || ($tkey[0] != "")) {
            foreach ($tkey as $k => $v) {
                if ($v && $v[0] == '?') {
                    return true;
                    //if (getHttpVars(substr($v,1),"-") == "-") return true;
                }
            }
        }
        return false;
    }

    /**
     * return true if the search need parameters
     */
    public function needParameters()
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        if ((count($tkey) > 1) || (!empty($tkey[0]))) {
            foreach ($tkey as $k => $v) {
                if ($v && $v[0] == '?') {
                    if (getHttpVars(substr($v, 1), "-") == "-") {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Add parameters
     *
     * @param $l
     *
     * @return string
     */
    public function urlWhatEncodeSpec($l)
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");

        if ((count($tkey) > 1) || (isset($tkey[0]) && $tkey[0] != "")) {
            foreach ($tkey as $k => $v) {
                if ($v && $v[0] == '?') {
                    if (getHttpVars(substr($v, 1), "-") != "-") {
                        $l .= '&' . substr($v, 1) . "=" . getHttpVars(substr($v, 1));
                    }
                }
            }
        }

        return $l;
    }

    /**
     * add parameters in title
     */
    public function getCustomTitle()
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $l = "";
        if ((count($tkey) > 1) || (isset($tkey[0]) && $tkey[0] != "")) {
            $tl = array();
            foreach ($tkey as $k => $v) {
                if ($v && $v[0] == '?') {
                    $vh = getHttpVars(substr($v, 1), "-");
                    if (($vh != "-") && ($vh != "")) {
                        if (is_numeric($vh)) {
                            $fam = $this->getSearchFamilyDocument();
                            if ($fam) {
                                $oa = $fam->getAttribute($taid[$k]);
                                if ($oa && $oa->type == "docid") {
                                    $vh = $this->getTitle($vh);
                                }
                            }
                        }
                        $tl[] = $vh;
                    }
                }
            }
            if (count($tl) > 0) {
                $l = " (" . implode(", ", $tl) . ")";
            }
        }
        return $this->getRawValue("ba_title") . $l;
    }


    /**
     * return true if the sqlselect is writted by hand
     *
     * @return bool
     */
    public function isStaticSql()
    {
        return ($this->getRawValue("se_static") != "");
    }

    /**
     * return family use for search
     *
     * @return \Doc
     */
    private function getSearchFamilyDocument()
    {
        static $fam = null;
        if (!$fam) {
            $fam = DocManager::createTemporaryDocument($this->getRawValue("SE_FAMID", 1));
        }
        return $fam;
    }


    private function getMethodName($methodStr)
    {
        $parseMethod = new \ParseFamilyMethod();
        $parseMethod->parse($methodStr);
        $err = $parseMethod->getError();
        if ($err) {
            return '';
        }
        return $parseMethod->methodName;
    }

    public static function pgRegexpQuote($str)
    {
        /*
         * Escape Postgresql's regexp special chars into theirs UTF16 form "\u00xx"
        */
        return preg_replace_callback('/[.|*+?{}\[\]()\\\\^$]/u', function ($m) {
            return sprintf('\\u00%x', ord($m[0]));
        }, $str);
    }
}
