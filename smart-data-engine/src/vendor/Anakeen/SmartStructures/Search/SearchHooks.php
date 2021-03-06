<?php
/**
 * Document searches classes
 *
 */

namespace Anakeen\SmartStructures\Search;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\DocumentAccess;
use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;
use Anakeen\SmartStructures\Dir\DirLib;

class SearchHooks extends \Anakeen\SmartStructures\Profiles\PSearchHooks
{
    public $defDoctype = 'S';

    public $tol
        = array(
            "and" => "and", #N_("and")
            "or" => "or"
        ); #N_("or")


    /**
     * max recursive level
     *
     * @public int
     */
    public $folderRecursiveLevel = 2;


    public function preConsultation()
    {
        $famId = $this->getRawValue(\SmartStructure\Fields\Search::se_famid);
        if ($famId) {
            $doc = SEManager::getFamily(abs($famId));
            if (!is_object($doc) || !$doc->isAlive() || $doc->defDoctype != 'C') {
                $err = sprintf(_('Family [%s] not found'), abs($famId));
                return $err;
            }
        }
        return '';
    }

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::PRECREATED, function () {
            return $this->updateSearchAuthor();
        })->addListener(SmartHooks::PREREFRESH, function () {
            return $this->verifyQuery();
        });
    }

    /**
     * the author is the current user if not already set
     *
     * @return string
     */
    protected function updateSearchAuthor()
    {
        $err = '';
        if (!$this->getRawValue("se_author")) {
            $err = $this->setValue("se_author", ContextManager::getCurrentUser()->fid);
        }
        return $err;
    }

    /**
     * to affect a special query to a SEARCH document
     * must be call after the add method When use this method others filter parameters are ignored.
     *
     * @param string $tquery the sql query
     *
     * @return string error message (empty if no error)
     */
    public function addStaticQuery($tquery)
    {
        $this->setValue("se_static", "1");
        $err = $this->addQuery($tquery);
        return $err;
    }

    public function addQuery($tquery)
    {
        // insert query in search document
        if (is_array($tquery)) {
            $query = implode(";\n", $tquery);
        } else {
            $query = $tquery;
        }

        if ($query == "") {
            return "";
        }
        if ($this->id == "") {
            return "";
        }

        $oqd = new \QueryDir($this->dbaccess);
        $oqd->dirid = $this->id;
        $oqd->qtype = "M"; // multiple
        $oqd->query = $query;

        if ($this->id > 0) {
            $this->query("delete from fld where dirid=" . intval($this->id) . " and qtype='M'");
        }
        $err = $oqd->add();
        if ($err == "") {
            $this->setValue("SE_SQLSELECT", $query);
            $err = $this->modify();
        }

        return $err;
    }

    /**
     * Test if current user can add or delete document in this folder
     * always false for a search
     *
     * @return string error message, if no error empty string
     */
    public function canModify()
    {
        return _("containt of searches cannot be modified");
    }

    /**
     * return true if the search has parameters
     */
    public function isParameterizable()
    {
        return false;
    }


    /**
     * return SQL query(ies) needed to search documents
     *
     * @return array string
     */
    public function getQuery()
    {
        if (!$this->isStaticSql()) {
            $query = $this->computeQuery(
                $this->getRawValue("se_key"),
                $this->getRawValue("se_famid"),
                $this->getRawValue("se_latest"),
                $this->getRawValue("se_case") == "yes",
                $this->getRawValue("se_idfld"),
                $this->getRawValue("se_sublevel") === ""
            );
            // print "<HR>getQuery1:[$query]";
        } else {
            $query[] = $this->getRawValue("SE_SQLSELECT");
        }

        return $query;
    }

    /**
     * Search on title
     * @param bool $full set to true if wan't use full text indexing
     */
    public function getSqlGeneralFilters($keyword, $latest, $sensitive)
    {
        $filters = array();

        $acls = $this->getMultipleRawValues("se_acl");
        if ((count($acls) > 0 && (ContextManager::getCurrentUser()->id != 1))) {
            //      print_r2($acls);
            foreach ($acls as $acl) {
                $dacl = DocumentAccess::$dacls[$acl];
                if ($dacl) {
                    $posacl = $dacl["pos"];
                    $filters[] = sprintf("hasaprivilege('%s', profid, %d)", \DocPerm::getMemberOfVector(ContextManager::getCurrentUser()->id), (1 << intval($posacl)));
                }
            }
        }

        if ($latest == "fixed") {
            $filters[] = "locked = -1";
            $filters[] = "lmodify = 'L'";
        } elseif ($latest == "allfixed") {
            $filters[] = "locked = -1";
        }
        if ($latest == "lastfixed") {
            $filters[] = "locked = -1";
        }



            $op = ($sensitive) ? '~' : '~*';
        if (strtolower(substr($keyword, 0, 5)) == "::get") { // only get method allowed
            // it's method call
            $keyword = $this->ApplyMethod($keyword);
            $filters[] = sprintf("title %s '%s'", $op, pg_escape_string($keyword));
        } elseif ($keyword != "") {
            // transform conjonction
            $tkey = explode(" ", $keyword);
            $ing = false;
            $ckey = '';
            foreach ($tkey as $k => $v) {
                if ($ing) {
                    if ($v[strlen($v) - 1] == '"') {
                        $ing = false;
                        $ckey .= " " . substr($v, 0, -1);
                        $filters[] = sprintf("title %s '%s'", $op, pg_escape_string($ckey));
                    } else {
                        $ckey .= " " . $v;
                    }
                } elseif ($v && $v[0] == '"') {
                    if ($v[strlen($v) - 1] == '"') {
                        $ckey = substr($v, 1, -1);
                        $filters[] = sprintf("title %s '%s'", $op, pg_escape_string($ckey));
                    } else {
                        $ing = true;
                        $ckey = substr($v, 1);
                    }
                } else {
                    $filters[] = sprintf("title %s '%s'", $op, pg_escape_string($v));
                }
            }
        }
            $this->setValue("se_orderby", " ");

        if ($this->getRawValue("se_sysfam") == 'no' && (!$this->getRawValue("se_famid"))) {
            $filters[] = sprintf("usefor !~ '^S'");
            $filters[] = sprintf("doctype != 'C'");
        }
        return $filters;
    }

    public function computeQuery($keyword = "", $famid = -1, $latest = "yes", $sensitive = false, $dirid = -1, $subfolder = true)
    {
        if ($dirid > 0) {
            if ($subfolder) {
                $cdirid = DirLib::getRChildDirId($this->dbaccess, $dirid, array(), 0, $this->folderRecursiveLevel);
            } else {
                $cdirid = $dirid;
            }
        } else {
            $cdirid = 0;
        }
        if ($keyword) {
            if ($keyword[0] == '~') {
                $full = false;
                $keyword = substr($keyword, 1);
            } elseif ($keyword[0] == '*') {
                $full = true;
                $keyword = substr($keyword, 1);
            }
        }
        $filters = $this->getSqlGeneralFilters($keyword, $latest, $sensitive);

        $only = '';
        if ($this->getRawValue("se_famonly") == "yes") {
            if (!is_numeric($famid)) {
                $famid = \Anakeen\Core\SEManager::getFamilyIdFromName($famid);
            }
            $only = "only";
        }

        $query = DirLib::getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters, false, $latest == "yes", $this->getRawValue("se_trash"), false, $level = 2, $join = '', $only);

        return $query;
    }

    /**
     * return true if the sqlselect is writted by hand
     *
     * @return bool
     */
    public function isStaticSql()
    {
        return ($this->getRawValue("se_static") != "")
            || (($this->getRawValue("se_latest") == "") && ($this->getRawValue("se_case") == "")
                && ($this->getRawValue("se_key") == ""));
    }

    /**
     * return error if query filters are not compatibles
     *
     * @return string error message , empty if no errors
     */
    public function getSqlParseError()
    {
        return "";
    }

    protected function verifyQuery()
    {
        $err = "";

        if (!$this->isStaticSql()) {
            if (!$this->isParameterizable()) {
                $query = $this->getQuery();
            } else {
                $query = 'select id from only doc where false';
            }
            $err = $this->AddQuery($query);
        }
        if ($err == "") {
            $err = $this->getSqlParseError();
        }
        return $err;
    }

    /**
     * return document includes in search folder
     *
     * @param bool  $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter      to add list sql filter for selected document
     * @param int   $famid       family identifier to restrict search
     *
     * @return array array of document array
     */
    public function getContent($controlview = true, array $filter = array(), $famid = "", $qtype = "TABLE", $trash = "")
    {
        if ($controlview) {
            $uid = ContextManager::getCurrentUser()->id;
        } else {
            $uid = 1;
        }
        $orderby = $this->getRawValue("se_orderby", "title");
        $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection(
            $this->dbaccess,
            $this->initid,
            0,
            "ALL",
            $filter,
            $uid,
            "TABLE",
            $famid,
            false,
            $orderby,
            true,
            $this->getRawValue("se_trash")
        );
        return $tdoc;
    }
}
