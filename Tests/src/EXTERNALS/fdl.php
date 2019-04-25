<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Functions used for edition help
 *
 * @author  Anakeen
 * @version $Id: FDL_external.php,v 1.71 2009/01/13 09:37:25 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */



/**
 * Functions used for edition help
 *
 * @param string $dbaccess database specification
 * @param int    $docid    identifier document
 *
 * @return array first item : the title
 */
function gettitle($dbaccess, $docid)
{

    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAffected()) {
        return array(
            $doc->title
        );
    }
    return array(
        "?",
        " "
    ); // suppress
}

/**
 * link enum definition from other def
 */
function linkenum($famid, $attrid)
{
    $soc = \Anakeen\Core\SEManager::getFamily($famid);
    if ($soc->isAffected()) {
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $a
         */
        $a = $soc->getAttribute($attrid);

        return EnumAttributeTools::getFlatEnumNotation($soc->id, $a->id);
    }
    return "";
}

/**
 * get mail address from MAILRECIPENT families
 *
 * @param $dbaccess
 * @param $name
 *
 * @return array|string
 */
function lmail($dbaccess, $name)
{

    $tr = array();
    $sf = new \Anakeen\Search\Internal\SearchSmartData($dbaccess, -1);
    $sf->setObjectReturn();
    $sf->overrideViewControl();
    $sf->addFilter("atags ~* 'MAILRECIPIENT'");
    $dlf = $sf->search()->getDocumentList();

    if ($dlf->count() == 0) {
        return sprintf(_("none families are described to be used as recipient"));
    }
    foreach ($dlf as $fam) {
        $cfam = createTmpDoc($dbaccess, $fam->id);
        /**
         * @var \Anakeen\Core\IMailRecipient $cfam
         */
        if (!method_exists($cfam, "getMail")) {
            return sprintf(_("family %s does not implement IMailRecipent - missing getMail method"), $fam->name);
        }
        if (!method_exists($cfam, "getMailAttribute")) {
            return sprintf(_("family %s does not implement IMailRecipent - missing getMailAttribute method"), $fam->name);
        }
        if (!method_exists($cfam, "getMailTitle")) {
            return sprintf(_("family %s does not implement IMailRecipient - missing getMailTitle method"), $fam->name);
        }

        $mailAttr = $cfam->getMailAttribute();
        $s = new \Anakeen\Search\Internal\SearchSmartData($dbaccess, $fam->id);
        $s->setObjectReturn();
        $s->setSlice(100);
        if ($mailAttr) {
            $s->addFilter("%s is not null", $mailAttr);
        }
        if ($name != "") {
            if ($mailAttr) {
                $s->addFilter("(title ~* '%s') or (%s ~* '%s')", $name, $mailAttr, $name);
            } else {
                $s->addFilter("(title ~* '%s')", $name, $name);
            }
        }
        $dl = $s->search()->getDocumentList();
        foreach ($dl as $dest) {
            /**
             * @var \SmartStructure\IUSER $dest
             */
            $mailTitle = $dest->getMailTitle();
            $mail = $dest->getMail();
            if ($mailTitle == '') {
                $mailTitle = $mail;
            }
            $usw = $dest->getRawValue("us_whatid");
            $uid = "";
            if ($usw > 0) {
                $uid = $dest->id;
                $type = "link"; //$type="link";  // cause it is a bool
            } else {
                $type = "plain"; //$type="plain";
                $uid = " ";
            }
            $tr[] = array(
                \Anakeen\Core\Utils\Strings::xmlEncode($mailTitle),
                $mail,
                $uid,
                $type
            );
        }
    }
    usort($tr, function ($a, $b) {
        return strcasecmp($a[0], $b[0]);
    });
    return $tr;
}

function tplmail($dbaccess, $type, $famid, $wfamid, $name)
{
    switch ($type) {
        case 'F': // address fix
            return lmail($dbaccess, $name);
            break;

        case 'A': // value of attribute
            if (!$famid) {
                return _("family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $famid, 'text', false, $name);
            return $ta;
            break;

        case 'D': // value of attribute
            if (!$famid) {
                return _("family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $famid, 'docid', false, $name);
            $ta = array_merge($ta, getFamAttribute($dbaccess, $famid, 'account', false, $name));
            return $ta;
            break;

        case 'DE':
            if (!$famid) {
                return _("family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $famid, 'docid', true, $name);
            $ta = array_merge($ta, getFamAttribute($dbaccess, $famid, 'account', true, $name));
            return $ta;
            break;

        case 'G': // value of attribute
            if (!$famid) {
                return _("family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $famid, 'file', false, $name);
            $ta = array_merge($ta, getFamAttribute($dbaccess, $famid, 'image', false, $name));
            return $ta;
            break;

        case 'E': // value of attribute
            if (!$famid) {
                return _("family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $famid, '', true, $name);
            return $ta;
        case 'WA': // value of attribute
            if (!$wfamid) {
                return _("cycle family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $wfamid, 'text', false, $name);
            return $ta;
            break;

        case 'WE': // value of attribute
            if (!$wfamid) {
                return _("cycle family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $wfamid, '', true, $name);
            return $ta;
            break;

        case 'WD': // value of attribute
            if (!$wfamid) {
                return _("cycle family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $wfamid, 'docid', false, $name);
            return $ta;
            break;

        case 'PR':
            if (!$famid) {
                return _("family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $famid, 'docid', true, $name);
            return $ta;
            break;

        case 'WPR':
            if (!$wfamid) {
                return _("cycle family must be defined");
            }
            $ta = getFamAttribute($dbaccess, $wfamid, 'docid', true, $name);
            return $ta;
            break;

        case 'P':
            return getGlobalsParameters($name);
            break;

        case 'RD':
            return recipientDocument($dbaccess, $name);
            break;
    }
    return "error tplmail($dbaccess,$type,$famid, $name)";
}

function tpluser($dbaccess, $type, $famid, $wfamid, $name)
{
    switch ($type) {
        case 'F': // address fix
            $users = lfamily($dbaccess, "IUSER", $name);
            if (is_array($users)) {
                foreach ($users as $k => $v) {
                    $users[$k][1] = $v[1] . ' (' . $v[2] . ')';
                }
            }
            return $users;
        default:
            return tplmail($dbaccess, $type, $famid, $wfamid, $name);
    }
}

function getGlobalsParameters($name)
{
    $q = new \Anakeen\Core\Internal\QueryDb("", \Anakeen\Core\Internal\ParamDef::class);

    $tr = array();
    $q->AddQuery("isglob = 'Y'");
    if ($name) {
        $q->AddQuery("name ~* '" . pg_escape_string($name) . "'");
    }
    $q->order_by = "name";
    $la = $q->Query(0, 20, "TABLE");
    foreach ($la as $k => $v) {
        $p = $v["name"] . ' (' . $v["descr"] . ')';
        $tr[] = array(
            $p,
            $p
        );
    }
    return $tr;
}

/**
 * attribut list to be use in mail template
 */
function getFamAttribute($dbaccess, $famid, $type = "text", $param = false, $name = "")
{
    $doc = createDoc($dbaccess, $famid, false);
    $tr = array();
    if ($param) {
        $tinter = $doc->getParamAttributes();
    } else {
        $tinter = $doc->GetNormalAttributes();
    }
    $name = strtolower($name);
    // HERE HERE HERE
    $pattern_name = preg_quote($name, "/");
    $pattern_type = ($type);
    foreach ($tinter as $k => $v) {
        if (($name == "") || (preg_match("/$pattern_name/i", $v->getLabel(), $reg)) || (preg_match("/$pattern_name/", $v->id, $reg))) {
            preg_match("/$pattern_type/", $v->type, $reg);
            if (($type == "") || ($v->type == $type) || ((strpos($type, '|') > 0) && (preg_match("/$pattern_type/", $v->type, $reg)))) {
                $r = $v->id . ' (' . $v->getLabel() . ')';
                $tr[] = array(
                    $r,
                    $r
                );
            }
        }
    }
    return $tr;
}

// liste des familles
function lfamilies($dbaccess, $name = '', $subfam = "")
{
    //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
    global $action;

    if ($subfam == "") {
        $tinter = \Anakeen\SmartStructures\Dir\DirLib::getClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    } else {
        if (!is_numeric($subfam)) {
            $subfam = \Anakeen\Core\SEManager::getFamilyIdFromName($subfam);
        }
        $cdoc = new_Doc($dbaccess, $subfam);
        $tinter = $cdoc->GetChildFam();
        $tinter[] = get_object_vars($cdoc);
    }

    $tr = array();

    $name = strtolower($name);
    // HERE HERE HERE
    $pattern_name = preg_quote($name, "/");
    foreach ($tinter as $v) {
        $ftitle = \Anakeen\Core\SmartStructure::getLangTitle($v);
        if (($name == "") || (preg_match("/$pattern_name/i", $ftitle, $reg))) {
            $tr[] = array(
                $ftitle,
                $v["id"],
                $ftitle
            );
        }
    }
    return $tr;
}

// liste des documents par familles

/**
 * list of documents of a same family
 *
 * @param string $dbaccess      database specification
 * @param string $famid         family identifier (if 0 any family). It can be internal name
 * @param string $name          string filter on the title
 * @param int    $dirid         identifier of folder for restriction to a folder tree (deprecated)
 * @param array  $filter        additionnals SQL filters
 * @param string $idid          the document id to use (default: id)
 * @param bool   $withDiacritic to search with accent
 *
 * @return array/string*3 array of (title, identifier, title)
 */
function lfamily($dbaccess, $famid, $name = "", $dirid = 0, $filter = array(), $idid = "id", $withDiacritic = false)
{
    $only = false;
    if ($famid[0] == '-') {
        $only = true;
        $famid = substr($famid, 1);
    }

    if (!is_numeric($famid)) {
        $famName = $famid;
        $famid = \Anakeen\Core\SEManager::getFamilyIdFromName($famName);
        if ($famid <= 0) {
            return sprintf(_("family %s not found"), $famName);
        }
    }
    $s = new \Anakeen\Search\Internal\SearchSmartData($dbaccess, $famid); //$famid=-(abs($famid));
    if ($only) {
        $s->only = true;
    }
    if (!is_array($filter)) {
        if (trim($filter) != "") {
            $filter = array(
                $filter
            );
        } else {
            $filter = array();
        }
    }
    if (count($filter) > 0) {
        foreach ($filter as $f) {
            $s->addFilter($f);
        }
    }

    if ($name != "" && is_string($name)) {
        if (!$withDiacritic) {
            $name = setDiacriticRules(mb_strtolower($name));
        }
        $s->addFilter("title ~* '%s'", $name);
    }
    $s->setSlice(100);

    if ($dirid) {
        $s->useCollection($dirid);
    }
    $s->returnsOnly(array(
        "title",
        $idid
    ));
    $tinter = $s->search();
    if ($s->getError()) {
        return $s->getError();
    }

    $tr = array();

    foreach ($tinter as $k => $v) {
        $tr[] = array(
            htmlspecialchars($v["title"]),
            $v[$idid],
            $v["title"]
        );
    }
    return $tr;
}

/**
 * create preg rule to search without diacritic
 *
 * @see lfamily
 *
 * @param string $text
 *
 * @return string rule for preg
 */
function setDiacriticRules($text)
{
    $dias = array(
        "a|à|á|â|ã|ä|å",
        "o|ò|ó|ô|õ|ö|ø",
        "e|è|é|ê|ë",
        "c|ç",
        "i|ì|í|î|ï",
        "u|ù|ú|û|ü",
        "y|ÿ",
        "n|ñ"
    );
    foreach ($dias as $dia) {
        $text = preg_replace("/[" . str_replace("|", "", $dia) . "]/u", "[$dia]", $text);
    }
    return $text;
}

// alias name

/**
 * @deprecated use lfamily instead
 *
 * @param        $dbaccess
 * @param        $famid
 * @param string $name
 * @param int    $dirid
 * @param array  $filter
 * @param string $idid
 *
 * @return mixed
 */
function lfamilly($dbaccess, $famid, $name = "", $dirid = 0, $filter = array(), $idid = "id")
{
    return lfamily($dbaccess, $famid, $name, $dirid, $filter, $idid);
}

/**
 * list of documents of a same family and their specific attributes
 *
 * @param string $dbaccess database specification
 * @param string $famid    family identifier (if 0 any family). It can be internal name
 * @param string $name     string filter on the title
 * @param string $attrids  argument variable of name of attribute to be returned
 *
 * @return array/string*3 array of (title, identifier, attr1, attr2, ...)
 */
function lfamilyvalues($dbaccess, $famid, $name = "")
{
    //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
    global $action;

    $only = false;
    if ($famid[0] == '-') {
        $only = true;
        $famid = substr($famid, 1);
    }

    if (!is_numeric($famid)) {
        $famid = \Anakeen\Core\SEManager::getFamilyIdFromName($famid);
    }
    $filter = array();
    if ($name != "") {
        $name = pg_escape_string($name);
        $filter[] = "title ~* '$name'";
    }
    $attr = array();
    $args = func_get_args();
    foreach ($args as $k => $v) {
        if ($k > 2) {
            $attr[] = strtolower($v);
        }
    }
    //$famid=-(abs($famid));
    if ($only) {
        $famid = -($famid);
    }
    $tinter = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection($dbaccess, $dirid = 0, 0, 100, $filter, $action->user->id, "TABLE", $famid, false, "title");

    $tr = array();

    foreach ($tinter as $k => $v) {
        $tr[$k] = array(
            $v["title"]
        );
        foreach ($attr as $a) {
            $tr[$k][] = $v[$a];
        }
    }
    return $tr;
}


