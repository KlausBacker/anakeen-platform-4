<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Function Utilities for freedom
 *
 * @author  Anakeen
 * @version $Id: LegacyDocManager.php,v 1.119 2009/01/20 14:30:39 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once("FDL/Lib.Util.php");

use Anakeen\Core\SEManager;
use Anakeen\Core\DbManager;

//
// ------------------------------------------------------
// construction of a sql disjonction
// ------------------------------------------------------
function GetSqlCond2($Table, $column)
// ------------------------------------------------------
{
    $sql_cond = "";
    if (count($Table) > 0) {
        $sql_cond = "(($column = '$Table[0]') ";
        for ($i = 1; $i < count($Table); $i++) {
            $sql_cond = $sql_cond . "OR ($column = '$Table[$i]') ";
        }
        $sql_cond = $sql_cond . ")";
    }

    return $sql_cond;
}

/**
 * @deprecated use DbManager::getSqlOrCond
 * @return string
 */
function GetSqlCond($Table, $column, $integer = false)
// ------------------------------------------------------
{
    return DbManager::getSqlOrCond($Table, $column, $integer);
}

/**
 * return first element of array
 *
 * @param array $a
 *
 * @return string the first, false is empty
 */
function first($a)
{
    if (count($a) == 0) {
        return false;
    }
    reset($a);
    return current($a);
}

function notEmpty($a)
{
    return (!empty($a));
}

/**
 * clear all cache used by new_doc function
 *
 * @deprecated use SEManager::cache()
 *
 * @param int $id document identifier : limit to destroy cache of only this document
 *
 * @return void
 */
function clearCacheDoc($id = 0)
{
    if ($id == 0) {
        SEManager::cache()->clear();
    } else {
        SEManager::cache()->removeDocumentById($id);
    }
}

/**
 * return document object in type concordance
 *
 * @param string     $dbaccess database specification
 * @param int|string $id       identifier of the object
 * @param bool       $latest   if true set to latest revision of doc
 *
 * @return \Anakeen\Core\Internal\SmartElement object
 * @throws DocManager\Exception
 * @throws \Dcp\Core\Exception
 * @deprecated use SEManager::getDocument
 *
 * @code
 * $myDoc=new_doc("", $myIdentifier);
 * if ($myDoc->isAlive()) {
 *     print $myDoc->getTitle();
 * } else {
 *     printf("%s not found",$myIdentifier);
 * }
 * @endcode
 *
 */
function new_Doc($dbaccess, $id = '', $latest = false)
{
    $doc = SEManager::getDocument($id, $latest);
    if (!$doc) {
        $doc = new \Anakeen\SmartStructures\Document($dbaccess);
    } else {
        if (count(\Dcp\Core\SharedDocuments::getKeys()) < \Dcp\Core\SharedDocuments::getLimit()) {
            SEManager::cache()->addDocument($doc);

            // var_dump([memory_get_usage(), count(\Dcp\Core\SharedDocuments::getKeys()),  \Dcp\Core\SharedDocuments::getLimit()]);
        }
    }

    return ($doc);
}

/**
 * create a new document object in type concordance
 *
 * the document is set with default values and default profil of the family
 *
 * @deprecated use SEManager::createDocument
 *
 * @param string $dbaccess      database specification
 * @param string $fromid        identifier of the family document (the number or internal name)
 * @param bool   $control       if false don't control the user hability to create this kind of document
 * @param bool   $defaultvalues if false not affect default values
 * @param bool   $temporary     if true create document as temporary doc (use \Anakeen\Core\Internal\SmartElement::createTmpDoc instead)
 *
 * @return \Anakeen\Core\Internal\SmartElement |false may be return false if no hability to create the document
 * @throws \Dcp\Core\Exception
 * @see        createTmpDoc to create temporary/working document
 * @code
 * $myDoc=createDoc("", "SOCIETY");
 * if ($myDoc) {
 *     $myDoc->setValue("si_name", "my company");
 *     $err=$myDoc->store();
 * }
 * @endcode
 */
function createDoc($dbaccess, $fromid, $control = true, $defaultvalues = true, $temporary = false)
{
    try {
        if ($temporary) {
            $doc = SEManager::createTemporaryDocument($fromid, $defaultvalues);
        } else {
            if ($control) {
                $doc = \Anakeen\SmartElementManager::createDocument($fromid, $defaultvalues);
            } else {
                $doc = SEManager::createDocument($fromid, $defaultvalues);
            }
        }
    } catch (\Dcp\Core\Exception $e) {
        if ($e->getCode() === "APIDM0003") {
            return false;
        }
        throw $e;
    }
    return $doc;
}

/**
 * create a temporary  document object in type concordance
 *
 * the document is set with default values and has no profil
 * the create privilege is not tested in this case
 *
 * @deprecated use SEManager::createTemporaryDocument
 *
 * @param string $dbaccess     database specification
 * @param string $fromid       identifier of the family document (the number or internal name)
 * @param bool   $defaultvalue set to false to not set default values
 *
 * @return \Anakeen\Core\Internal\SmartElement may be return false if no hability to create the document
 */
function createTmpDoc($dbaccess, $fromid, $defaultvalue = true)
{
    $d = createDoc($dbaccess, $fromid, false, $defaultvalue, true);
    if ($d) {
        $d->doctype = 'T'; // tag has temporary document
        $d->profid = 0; // no privilege
    }
    return $d;
}

/**
 * return from id for document (not for family (use @see getFamFromId() instead)
 *
 * @deprecated use SEManager::getFromId(
 *
 * @param string $dbaccess database specification
 * @param int    $id       identifier of the object
 *
 * @return int|false false if error occured (return -1 if family document )
 */
function getFromId($dbaccess, $id)
{
    if (!($id > 0)) {
        return false;
    }
    if (!is_numeric($id)) {
        return false;
    }

    $fromid = SEManager::getFromId($id);
    if (!$fromid) {
        return false;
    }

    return $fromid;
}

/**
 * return from name for document (not for family (use @see getFamFromId() instead)
 *
 * @deprecated use SEManager::getFromName()
 *
 * @param string $dbaccess database specification
 * @param int    $id       identifier of the object
 *
 * @return string false if error occured (return -1 if family document )
 */
function getFromName($dbaccess, $id)
{
    if (!($id > 0)) {
        return false;
    }
    if (!is_numeric($id)) {
        return false;
    }

    $fromname = SEManager::getFromName($id);
    if (!$fromname) {
        return false;
    }

    return $fromname;
}


/**
 * get document title from document identifier
 *
 * @param int|string $id     document identifier
 * @param bool       $latest set to false for a fixed id or true for latest
 *
 * @return string
 */
function getDocTitle($id, $latest = true)
{
    if (!is_numeric($id)) {
        $id = \Anakeen\Core\SEManager::getIdFromName($id);
    }
    if ($id > 0) {
        if (!$latest) {
            $sql = sprintf("select title, doctype, locked, initid, name from docread where id=%d", $id);
        } else {
            $sql
                = sprintf(
                "select title, doctype, locked, initid, name from docread where initid=(select initid from docread where id=%d) order by id desc limit 1",
                $id
            );
        }
        DbManager::query($sql, $t, false, true);

        if (!$t) {
            return '';
        }
        if ($t["doctype"] == 'C') {
            return getFamTitle($t);
        }
        // TODO confidential property
        return $t["title"];
    }
    return '';
}

/**
 * get some properties for a document
 *
 * @param       $id
 * @param bool  $latest
 * @param array $prop properties list to retrieve
 *
 * @return array|null of indexed properties's values - empty array if not found
 */
function getDocProperties(
    $id,
    $latest = true,
    array $prop
    = array(
        "title"
    )
) {
    if (!is_numeric($id)) {
        $id = \Anakeen\Core\SEManager::getIdFromName($id);
    }
    if (($id > 0) && count($prop) > 0) {
        $sProps = implode(',', $prop);
        if (!$latest) {
            $sql = sprintf("select %s, doctype, locked, initid from docread where id=%d", $sProps, $id);
        } else {
            $sql
                = sprintf(
                "select %s, doctype, locked, initid from docread where initid=(select initid from docread where id=%d) order by id desc limit 1",
                $sProps,
                $id
            );
        }
        DbManager::query($sql, $t, false, true);

        if (!$t) {
            return null;
        }
        return $t;
    }
    return null;
}

/**
 * return document table value
 *
 * @deprecated use Anakeen\Core\SEManager::getRawDocument(), Anakeen\Core\SEManager::getRawData()
 *
 * @param string $dbaccess   database specification
 * @param int    $id         identifier of the object
 * @param array  $sqlfilters add sql supply condition
 *
 * @param array  $result
 *
 * @return array|false false if error occured or if cocument not found
 */
function getTDoc($dbaccess, $id, $sqlfilters = array(), $result = array())
{
    global $action;
    global $SQLDELAY, $SQLDEBUG;

    if (!is_numeric($id)) {
        $id = \Anakeen\Core\SEManager::getIdFromName($id);
    }
    if (!($id > 0)) {
        return false;
    }
    $dbid = DbManager::getDbId();
    $table = "doc";
    $fromid = \Anakeen\Core\SEManager::getFromId($id);
    if ($fromid > 0) {
        $table = "doc$fromid";
    } else {
        if ($fromid == -1) {
            $table = "docfam";
        }
    }
    if ($fromid == 0) {
        return false;
    } // no document can be found
    $sqlcond = "";
    if (count($sqlfilters) > 0) {
        $sqlcond = "and (" . implode(") and (", $sqlfilters) . ")";
    }
    if (count($result) == 0) {
        $userMemberOf = DocPerm::getMemberOfVector();
        $sql = sprintf(
            "select *,getaperm('%s',profid) as uperm from only %s where id=%d %s",
            $userMemberOf,
            $table,
            $id,
            $sqlcond
        );
    } else {
        $scol = implode($result, ",");
        $sql = "select $scol from only $table where id=$id $sqlcond;";
    }
    $sqlt1 = 0;
    if ($SQLDEBUG) {
        $sqlt1 = microtime();
    } // to test delay of request
    $result = pg_query($dbid, $sql);
    if ($SQLDEBUG) {
        global $TSQLDELAY;
        $SQLDELAY += microtime_diff(microtime(), $sqlt1); // to test delay of request
        $TSQLDELAY[] = array(
            "t" => sprintf("%.04f", microtime_diff(microtime(), $sqlt1)),
            "s" => $sql
        );
    }
    if (($result) && (pg_num_rows($result) > 0)) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);

        return $arr;
    }
    return false;
}

/**
 * return the value of an doc array item
 *
 * @param array  &$t the array where get value
 * @param string $k  the index of the value
 * @param string $d  default value if not found or if it is empty
 *
 * @return string
 */
function getv(&$t, $k, $d = "")
{
    if (isset($t[$k]) && ($t[$k] != "")) {
        return $t[$k];
    }
    if (strpos($t["attrids"], "£$k") !== 0) {
        $tvalues = explode("£", $t["values"]);
        $tattrids = explode("£", $t["attrids"]);
        foreach ($tattrids as $ka => $va) {
            if ($va != "") {
                if (!isset($t[$va])) {
                    $t[$va] = $tvalues[$ka];
                }
                if ($va == $k) {
                    if ($tvalues[$ka] != "") {
                        return $tvalues[$ka];
                    }
                    break;
                }
            }
        }
    }
    return $d;
}

/**
 * complete all values of an doc array item
 *
 * @param array &$t the array where get value
 *
 * @return string[]
 */
function getvs(&$t)
{
    $tvalues = explode("£", $t["values"]);
    $tattrids = explode("£", $t["attrids"]);
    foreach ($tattrids as $ka => $va) {
        if ($va != "") {
            if (!isset($t[$va])) {
                $t[$va] = $tvalues[$ka];
            }
        }
    }
    return $t;
}

/**
 * use to usort attributes
 *
 * @param \Anakeen\Core\SmartStructure\BasicAttribute $a
 * @param \Anakeen\Core\SmartStructure\BasicAttribute $b
 */
function tordered($a, $b)
{
    if (isset($a->ordered) && isset($b->ordered)) {
        if ($a->ordered == $b->ordered) {
            return 0;
        }
        if ($a->ordered > $b->ordered) {
            return 1;
        }
        return -1;
    }
    if (isset($a->ordered)) {
        return 1;
    }
    if (isset($b->ordered)) {
        return -1;
    }
    return 0;
}

function cmp_cvorder3($a, $b)
{
    if ($a["cv_order"] == $b["cv_order"]) {
        return 0;
    }
    return ($a["cv_order"] < $b["cv_order"]) ? -1 : 1;
}

/**
 * control privilege for a document in the array form
 * the array must provide from getTdoc
 * the function is equivalent of \Anakeen\Core\Internal\SmartElement::Control
 *
 * @param array  $tdoc    document
 * @param string $aclname identifier of the privilege to test
 *
 * @return bool true if current user has privilege
 */
function controlTdoc(&$tdoc, $aclname)
{
    global $action;
    static $_ODocCtrol = false;
    static $_memberOf = false; // current user
    if (!$_ODocCtrol) {
        $_ODocCtrol = true;
        $_memberOf = DocPerm::getMemberOfVector();
    }

    if (($tdoc["profid"] <= 0) || ($action->user->id == 1)) {
        return true;
    }
    if (!isset($tdoc["uperm"])) {
        $sql = sprintf("select getaperm('%s',%d) as uperm", $_memberOf, $tdoc['profid']);
        DbManager::query($sql, $uperm, true, true);

        $tdoc["uperm"] = $uperm;
    }


    return (\Anakeen\Core\Internal\DocumentAccess::hasControl($tdoc["uperm"], $aclname));
}

/**
 * get document object from array document values
 *
 * @deprecated use SEManager::getDocumentFromRawDocument
 *
 * @param string $dbaccess database specification
 * @param array  $v        values of document
 *
 * @return \Anakeen\Core\Internal\SmartElement the document object
 */
function getDocObject($dbaccess, $v, $k = 0)
{
    /* @var \Anakeen\Core\Internal\SmartElement [][] $_OgetDocObject */
    static $_OgetDocObject;

    if ($v["doctype"] == "C") {
        if (!isset($_OgetDocObject[$k]["family"])) {
            $_OgetDocObject[$k]["family"] = new \Anakeen\Core\SmartStructure($dbaccess);
        }
        $_OgetDocObject[$k]["family"]->Affect($v, true);
        $v["fromid"] = "family";
    } else {
        if (!isset($_OgetDocObject[$k][$v["fromid"]])) {
            $_OgetDocObject[$k][$v["fromid"]] = createDoc($dbaccess, $v["fromid"], false, false);
        }
    }

    $_OgetDocObject[$k][$v["fromid"]]->Affect($v, true);

    return $_OgetDocObject[$k][$v["fromid"]];
}

/**
 * return the next document in sql select ressources
 * use with "ITEM" type searches direct in QueryDb
 * return \Anakeen\Core\Internal\SmartElement the next doc (false if the end)
 */
function getNextDbObject($dbaccess, $res)
{
    $tdoc = pg_fetch_array($res, null, PGSQL_ASSOC);
    if ($tdoc === false) {
        return false;
    }
    return getDocObject($dbaccess, $tdoc, intval($res));
}

/**
 * return the next document in sql select ressources
 * use with "ITEM" type searches with getChildDoc
 * return \Anakeen\Core\Internal\SmartElement the next doc (false if the end)
 */
function getNextDoc($dbaccess, &$tres)
{
    $n = current($tres);
    if ($n === false) {
        return false;
    }
    $tdoc = pg_fetch_array($n, null, PGSQL_ASSOC);
    if ($tdoc === false) {
        $n = next($tres);
        if ($n === false) {
            return false;
        }
        $tdoc = pg_fetch_array($n, null, PGSQL_ASSOC);
        if ($tdoc === false) {
            return false;
        }
    }
    return getDocObject($dbaccess, $tdoc, intval(current($tres)));
}

/**
 * count returned document in sql select ressources
 *
 * @param array $tres of ressources
 *                    return \Anakeen\Core\Internal\SmartElement the next doc (false if the end)
 */
function countDocs(&$tres)
{
    $n = 0;
    foreach ($tres as $res) {
        $n += pg_num_rows($res);
    }
    reset($tres);
    return $n;
}

/**
 * return the identifier of a family from internal name
 *
 * @deprecated use SEManager::getFamilyIdFromName
 *
 * @param string $dbaccess database specification
 * @param string $name     internal family name
 *
 * @return int 0 if not found
 */
function getFamIdFromName($dbaccess, $name)
{
    return SEManager::getFamilyIdFromName($name);
}

/**
 * return the identifier of a document from a search with title
 *
 * @param string  $dbaccess database specification
 * @param string  $name     logical name
 * @param string  $famid    must be set to increase speed search
 * @param boolean $only     set to true to not search in subfamilies
 *
 * @return int 0 if not found, return negative first id found if multiple (name must be unique)
 */
function getIdFromTitle($dbaccess, $title, $famid = "", $only = false)
{
    if ($famid && (!is_numeric($famid))) {
        $famid = SEManager::getFamilyIdFromName($famid);
    }
    if ($famid > 0) {
        $fromonly = ($only) ? "only" : "";
        $err = DbManager::query(sprintf(
            "select id from $fromonly doc%d where title='%s' and locked != -1",
            $famid,
            pg_escape_string($title)
        ), $id, true, true);
    } else {
        $err = DbManager::query(sprintf(
            "select id from docread where title='%s' and locked != -1",
            pg_escape_string($title)
        ), $id, true, true);
    }

    return $id;
}

/**
 * return the latest identifier of a document from its logical name
 *
 * @deprecated use SEManager::getIdFromName
 *
 * @param string $dbaccess database specification
 * @param string $name     logical name
 *
 * @return string|false return numeric id, false if not found, if revision (name must be unique) return the latest id
 */
function getIdFromName($dbaccess, $name)
{
    try {
        $id = (string)SEManager::getIdFromName($name);
        if ($id === "0") {
            $id = false;
        }
    } catch (Exception $e) {
        $id = false;
    }
    return $id;
}

/**
 * return the initial identifier of a document from its logical name
 *
 * @deprecated use SEManager::getInitIdFromName
 *
 * @param string $name
 *
 * @return int
 */
function getInitidFromName($name)
{
    return SEManager::getInitIdFromName($name);
}

/**
 * return the logical name of a document from its initial identifier
 *
 * @deprecated use SEManager::getNameFromId
 *
 * @param string $dbaccess database specification
 * @param string $id       initial identifier
 *
 * @return string empty if not found
 */
function getNameFromId($dbaccess, $id)
{
    return SEManager::getNameFromId($id);
}

/**
 * return freedom user document in concordance with what user id
 *
 * @param string $dbaccess database specification
 * @param int    $userid   what user identifier
 *
 * @return \Anakeen\Core\Internal\SmartElement |false the user document
 */
function getDocFromUserId($dbaccess, $userid)
{
    if ($userid == "") {
        return false;
    }
    $tdoc = array();
    $user = new \Anakeen\Core\Account("", $userid);
    if (!$user->isAffected()) {
        return false;
    }
    if ($user->accounttype == \Anakeen\Core\Account::GROUP_TYPE) {
        $filter = array(
            "us_whatid = '$userid'"
        );
        $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection(
            $dbaccess,
            0,
            0,
            "ALL",
            $filter,
            1,
            "LIST",
            SEManager::getFamilyIdFromName("IGROUP")
        );
    } else {
        $filter = array(
            "us_whatid = '$userid'"
        );
        $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection(
            $dbaccess,
            0,
            0,
            "ALL",
            $filter,
            1,
            "LIST",
            SEManager::getFamilyIdFromName("IUSER")
        );
    }
    if (count($tdoc) == 0) {
        return false;
    }
    return $tdoc[0];
}

function getFamTitle(&$tdoc)
{
    $r = $tdoc["name"] . '#title';
    $i = _($r);
    if ($i != $r) {
        return $i;
    }
    return $tdoc['title'];
}

/**
 * verify in database if document is fixed
 *
 * @return bool
 */
function isFixedDoc($dbaccess, $id)
{
    $tdoc = SEManager::getRawData($id, ["locked"], false, false);

    if (!$tdoc) {
        return null;
    }
    return ($tdoc["locked"] == -1);
}




/**
 * return doc array of latest revision of initid
 *
 * @deprecated use SEManager::getRawDocument()
 *
 * @param string $dbaccess   database specification
 * @param string $initid     initial identifier of the  document
 * @param array  $sqlfilters add sql supply condition
 *
 * @return array|false values array if found. False if initid not avalaible
 */
function getLatestTDoc($dbaccess, $initid, $sqlfilters = array(), $fromid = false)
{
    global $action;

    if (!($initid > 0)) {
        return false;
    }
    DbManager::getDbId();
    $table = "doc";
    if (!$fromid) {
        DbManager::query(sprintf("select fromid from docread where initid=%d order by id desc", $initid), $tf, true);
        if (count($tf) > 0) {
            $fromid = $tf[0];
        }
    }
    if ($fromid > 0) {
        $table = "doc$fromid";
    } else {
        if ($fromid == -1) {
            $table = "docfam";
        }
    }

    $sqlcond = "";
    if (count($sqlfilters) > 0) {
        $sqlcond = "and (" . implode(") and (", $sqlfilters) . ")";
    }

    $userid = $action->user->id;
    if ($userid) {
        $userMember = DocPerm::getMemberOfVector();
        $sql = sprintf(
            "select *,getaperm('%s',profid) as uperm  from only %s where initid=%d and doctype != 'T' and locked != -1 %s",
            $userMember,
            $table,
            $initid,
            $sqlcond
        );
        DbManager::query($sql, $result);
        if (!$result) {
            // zombie doc ?
            $sql = sprintf(
                "select *,getaperm('%s',profid) as uperm  from only %s where initid=%d and doctype != 'T' %s order by id desc limit 1",
                $userMember,
                $table,
                $initid,
                $sqlcond
            );
            DbManager::query($sql, $result);
        }

        if ($result && (count($result) > 0)) {
            if (count($result) > 1) {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf("document %d : multiple alive revision", $initid));
            }

            $arr = $result[0];

            return $arr;
        }
    }
    return false;
}

/**
 * return identificators according to latest revision
 * the order is not the same as parameters. The key of result containt initial id
 *
 * @param string $dbaccess database specification
 * @param array  $ids      array of document identificators
 *
 * @return array identifier relative to latest revision. if one or several documents document not exists the identifier not appear in result so the array count of result can be lesser than parameter
 */
function getLatestDocIds($dbaccess, $ids)
{
    if (!is_array($ids)) {
        return null;
    }

    $dbid = DbManager::getDbId();
    foreach ($ids as $k => $v) {
        $ids[$k] = intval($v);
    }
    $sids = implode($ids, ",");
    $sql
        = sprintf(
        "SELECT id,initid from docread where initid in (SELECT initid from docread where id in (%s)) and locked != -1;",
        $sids
    );
    $result = @pg_query($dbid, $sql);
    if ($result) {
        $arr = pg_fetch_all($result);
        $tlids = array();
        foreach ($arr as $v) {
            $tlids[$v["initid"]] = $v["id"];
        }
        return $tlids;
    }
    return null;
}

/**
 * return latest id of document from its initid or other id
 *
 * @param string $dbaccess database specification
 * @param int    $initid   document identificator
 *
 * @return int identifier relative to latest revision. if one or several documents document not exists the identifier not appear in result so the array count of result can be lesser than parameter
 */
function getLatestDocId($dbaccess, $initid)
{
    if (is_array($initid)) {
        return null;
    }
    // first more quick if alive
    DbManager::query(sprintf("select id from docread where initid='%d' and locked != -1", $initid), $id, true, true);
    if ($id > 0) {
        return $id;
    }
    // second for zombie document
    DbManager::query(
        sprintf("select id from docread where initid='%d' order by id desc limit 1", $initid),
        $id,
        true,
        true
    );
    if ($id > 0) {
        return $id;
    }
    // it is not really on initid
    DbManager::query(sprintf(
        "select id from docread where initid=(select initid from docread where id=%d) and locked != -1",
        $initid
    ), $id, true, true);
    if ($id > 0) {
        return $id;
    }
    return null;
}

/**
 * return doc array of specific revision of document initid
 *
 * @param string $dbaccess database specification
 * @param string $initid   initial identifier of the  document
 * @param int    $rev      revision number
 *
 * @return array values array if found. False if initid not avalaible
 */
function getRevTDoc($dbaccess, $initid, $rev)
{
    global $action;

    if (!($initid > 0)) {
        return false;
    }
    $table = "docread";
    $fromid = \Anakeen\Core\SEManager::getFromId($initid);
    $sql = sprintf("select fromid from docread where initid=%d and revision=%d", $initid, $rev);
    DbManager::query($sql, $fromid, true, true);
    if ($fromid > 0) {
        $table = "doc$fromid";
    } else {
        if ($fromid == -1) {
            $table = "docfam";
        }
    }

    $userMember = DocPerm::getMemberOfVector();
    $sql = sprintf(
        "select *,getaperm('%s',profid) as uperm from only %s where initid=%d and revision=%d ",
        $userMember,
        $table,
        $initid,
        $rev
    );
    DbManager::query($sql, $result, false, true);
    if ($result) {
        return $result;
    }
    return false;
}

/**
 * return really latest revision number
 * use only for debug mode
 *
 * @param string $dbaccess database specification
 * @param int    $initid   initial identifier of the  document
 * @param int    $fromid   family identicator of document
 *
 * @return int latest revision if found. False if initid not available
 */
function getLatestRevisionNumber($dbaccess, $initid, $fromid = 0)
{
    global $action;

    $initid = intval($initid);
    if (!($initid > 0)) {
        return false;
    }
    $dbid = DbManager::getDbId();
    $table = "docread";
    if ($fromid == -1) {
        $table = "docfam";
    }

    $result = @pg_query($dbid, "SELECT revision from $table where initid=$initid order by revision desc limit 1;");
    if ($result && (pg_num_rows($result) > 0)) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
        return $arr['revision'];
    }
    return false;
}

/**
 * Create default folder for a family with default constraint
 *
 * @param \Anakeen\Core\Internal\SmartElement $Doc the family object document
 *
 * @return int id of new folder (false if error)
 */
function createAutoFolder(&$doc)
{
    $dir = createDoc($doc->dbaccess, SEManager::getFamilyIdFromName("DIR"));
    $err = $dir->add();
    if ($err != "") {
        return false;
    }
    $dir->setValue("BA_TITLE", sprintf(_("root for %s"), $doc->title));
    $dir->setValue("BA_DESC", _("default folder"));
    $dir->setValue("FLD_ALLBUT", "1");
    $dir->setValue("FLD_FAM", $doc->title . "\n" . _("folder") . "\n" . _("search"));
    $dir->setValue(
        "FLD_FAMIDS",
        $doc->id . "\n" . SEManager::getFamilyIdFromName("DIR") . "\n" . SEManager::getFamilyIdFromName("SEARCH")
    );
    $dir->setValue("FLD_SUBFAM", "yes\nyes\nyes");
    $dir->Modify();
    $fldid = $dir->id;
    return $fldid;
}




/**
 * send simple query to database
 *
 * @deprecated use \Anakeen\Core\DbManager::query
 *
 * @param string            $dbaccess     access database coordonates (not used)
 * @param string            $query        sql query
 * @param string|bool|array &$result      query result
 * @param bool              $singlecolumn set to true if only one field is return
 * @param bool              $singleresult set to true is only one row is expected (return the first row).
 *                                        If is combined with singlecolumn return the value not an array,
 *                                        if no results and $singlecolumn is true then $results is false
 * @param bool              $useStrict    set to true to force exception or false to force no exception, if null use global parameter
 *
 * @throws Dcp\Db\Exception
 * @return string error message. Empty message if no errors (when strict mode is not enable)
 */
function simpleQuery(
    $dbaccess,
    $query,
    &$result = array(),
    $singlecolumn = false,
    $singleresult = false,
    $useStrict = null
) {
    static $sqlStrict = null;
    try {
        \Anakeen\Core\DbManager::query($query, $result, $singlecolumn, $singleresult);
    } catch (\Dcp\Db\Exception $e) {
        if ($useStrict !== false) {
            throw $e;
        }
        return $e->getMessage();
    }
    return "";
}