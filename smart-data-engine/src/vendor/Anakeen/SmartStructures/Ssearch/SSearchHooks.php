<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Special search
 */

namespace Anakeen\SmartStructures\Ssearch;

use Anakeen\Core\ContextManager;

class SSearchHooks extends \SmartStructure\Search
{
    public $defaultedit = "FDL:EDITBODYCARD";
    public $defaultview = "FDL:VIEWBODYCARD";
    /**
     * return sql query to search wanted document
     */
    public function computeQuery($keyword = "", $famid = - 1, $latest = "yes", $sensitive = false, $dirid = - 1, $subfolder = true, $full = false)
    {
        return true;
    }
    
    public function getDocList($start = 0, $slice = "ALL", $qtype = "TABLE", $userid = "")
    {
        $phpfile = $this->getRawValue("se_phpfile");
        $phpfunc = $this->getRawValue("se_phpfunc");
        $phparg = $this->getRawValue("se_phparg");
        if (!include_once("EXTERNALS/$phpfile")) {
            global $action;
            $action->AddWarningMsg(sprintf(_("php file %s needed for request not found"), "EXTERNALS/$phpfunc"));
            return false;
        }
        if (!$userid) {
            $userid = ContextManager::getCurrentUser()->id;
        }
        $arg = array(
            $start,
            $slice,
            $userid
        );
        if ($phparg != "") {
            $moreargs = explode(",", $phparg);
            foreach ($moreargs as $k => $v) {
                $v = trim($v);
                if ($v) {
                    if (($v[0] == "%") && (substr($v, -1) == "%")) {
                        $aid = substr($v, 1, -1);
                        if ($aid == "THIS") {
                            $moreargs[$k] = & $this;
                        } else {
                            $val = $this->getRawValue($aid);
                            if (!$val) {
                                $val = $this->getPropertyValue($aid);
                            }
                            $moreargs[$k] = $val;
                        }
                    }
                }
            }
            $arg = array_merge($arg, $moreargs);
        }
        $res = call_user_func_array($phpfunc, $arg);
        
        return ($res);
    }
    /**
     * return true if the search has parameters
     */
    public function isParameterizable()
    {
        return false;
    }
    /**
     * return true if the search need parameters
     */
    public function needParameters()
    {
        return false;
    }
    
    public function isStaticSql()
    {
        return true;
    }
    /**
     * return document includes in search folder
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int $famid family identifier to restrict search
     * @return array array of document array
     */
    
    public function getContent($controlview = true, array $filter = array(), $famid = "", $unusedType = "TABLE", $unusedTrash = "")
    {
        $uid = 0;
        if ($controlview) {
            $uid = 1;
        }
        return $this->getDocList(0, "ALL", $uid);
    }
    /**
     * return number of item in this searches
     * @return int -1 if errors
     */
    public function count()
    {
        $t = $this->getContent();
        if (is_array($t)) {
            return count($t);
        }
        return -1;
    }
}
