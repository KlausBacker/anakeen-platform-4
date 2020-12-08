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


use Anakeen\Core\SEManager;
use Anakeen\Core\DbManager;

/**
 * clear all cache used by new_doc function
 *
 * @deprecated use SEManager::cache()
 *
 * @param int $id document identifier : limit to destroy cache of only this document
 *
 * @return void
 */
function clearCacheDoc(int $id = 0)
{
    if ($id === 0) {
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
 * @throws \Anakeen\Core\Exception
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
        $doc = new \Anakeen\SmartElement($dbaccess);
    } else {
        if (count(\Anakeen\Legacy\SharedDocuments::getKeys()) < \Anakeen\Legacy\SharedDocuments::getLimit()) {
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
 * @throws \Anakeen\Core\Exception
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
    } catch (\Anakeen\Core\Exception $e) {
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
    if (empty($fromid)) {
        return false;
    } // no document can be found
    $sqlcond = "";
    if (count($sqlfilters) > 0) {
        $sqlcond = "and (" . implode(") and (", $sqlfilters) . ")";
    }
    if (count($result) === 0) {
        $userMemberOf = DocPerm::getMemberOfVector();
        $sql = sprintf(
            "select *,getaperm('%s',profid) as uperm from only %s where id=%d %s",
            $userMemberOf,
            $table,
            $id,
            $sqlcond
        );
    } else {
        $scol = implode(",", $result);
        $sql = "select $scol from only $table where id=$id $sqlcond;";
    }
    $sqlt1 = 0;
    if ($SQLDEBUG) {
        $sqlt1 = microtime();
    } // to test delay of request
    $result = pg_query($dbid, $sql);

    if (($result) && (pg_num_rows($result) > 0)) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);

        return $arr;
    }
    return false;
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
    static $_ODocCtrol = false;
    static $_memberOf = false; // current user
    if (!$_ODocCtrol) {
        $_ODocCtrol = true;
        $_memberOf = DocPerm::getMemberOfVector();
    }

    if (($tdoc["profid"] <= 0) || (\Anakeen\Core\ContextManager::getCurrentUser()->id == 1)) {
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
        DbManager::query(sprintf(
            "select id from $fromonly doc%d where title='%s' and locked != -1",
            $famid,
            pg_escape_string($title)
        ), $id, true, true);
    } else {
        DbManager::query(sprintf(
            "select id from docread where title='%s' and locked != -1",
            pg_escape_string($title)
        ), $id, true, true);
    }

    return $id;
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

    $userid = \Anakeen\Core\ContextManager::getCurrentUser()->id;
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
 * @return array identifier relative to latest revision. if one or
 * several documents document not exists the identifier not appear
 * in result so the array count of result can be lesser than parameter
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
    $sids = implode(",", $ids);
    $sql = sprintf(
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
 * @return int identifier relative to latest revision.
 * if one or several documents document not exists the identifier
 * not appear in result so the array count of result can be lesser than parameter
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
    $dir->setValue("FLD_FAM", [$doc->title, _("folder"), _("search")]);
    $dir->setValue(
        "FLD_FAMIDS",
        [$doc->id, SEManager::getFamilyIdFromName("DIR"), SEManager::getFamilyIdFromName("SEARCH")]
    );
    $dir->setValue("FLD_SUBFAM", ["yes", "yes", "yes"]);
    $dir->Modify();
    $fldid = $dir->id;
    return $fldid;
}


/**
 * send simple query to database
 *
 * @deprecated use \Anakeen\Core\DbManager::query
 *
 * @param string             $dbaccess     access database coordonates (not used)
 * @param string             $query        sql query
 * @param string|bool|array &$result       query result
 * @param bool               $singlecolumn set to true if only one field is return
 * @param bool               $singleresult set to true is only one row is expected (return the first row).
 *                                         If is combined with singlecolumn return the value not an array,
 *                                         if no results and $singlecolumn is true then $results is false
 * @param bool               $useStrict    set to true to force exception or false to force no exception, if null use global parameter
 *
 * @throws Anakeen\Database\Exception
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
    } catch (\Anakeen\Database\Exception $e) {
        if ($useStrict !== false) {
            throw $e;
        }
        return $e->getMessage();
    }
    return "";
}
