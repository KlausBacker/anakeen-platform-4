<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display parameters
 *
 * @author Anakeen
 * @version $Id: param_list.php,v 1.10 2005/06/16 12:23:07 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function param_list(Action & $action)
{
    // -----------------------------------
    // Get Param
    $userid = $action->getArgument("userid");
    $pview = $action->getArgument("pview"); // set to "all" or "single" if user parameters
    $mode = $action->getArgument("searchuser");
    
    if ($mode) {
        $value = "";
        if ($userid) {
            $u = new Account();
            $u->select($userid);
            $value = trim(sprintf("%s %s", $u->lastname, $u->firstname));
        }
        $action->lay->eSet("user_id", $userid);
        $action->lay->eSet("userlabel", $value);
    }
    // can chg action because of acl USER/ADMIN
    $action->lay->Set("ACTIONDEL", "PARAM_DELETE");
    $action->lay->Set("ACTIONMOD", "PARAM_MOD");
    
    $action->lay->eSet("userid", $userid);
    $action->lay->eSet("pview", $pview);
    $action->lay->set("searchuser", (bool)$mode);
    $action->lay->set("applabel", "");
    $action->lay->set("app_id", "");
}

function appmngGetParamListDatatableInfo(Action & $action)
{
    
    $sEcho = intval($action->getArgument('sEcho'));
    $out = array(
        "sEcho" => $sEcho
    );
    $filter = 0;
    
    $userid = $action->getArgument("userid");
    $pview = $action->getArgument("pview"); // set to "all" or "single" if user parameters
    $type = $action->getArgument("type");
    $appid = $action->getArgument("appid");
    $withStatic = $action->getArgument("withstatic");
    
    $tparam = array();
    $userParams = array();
    $paramType = "";
    $second_type = ($type == "system") ? "" : "or application.tag IS NULL";
    $type = ($type == "system") ? "~" : "!~";
    $filterQuery = "";
    if ($appid) {
        $filterQuery = sprintf(" and application.id=%d", pg_escape_string($appid));
    }
    for ($index = 0; $index < $action->getArgument('iColumns'); $index++) {
        $search = $action->getArgument('sSearch_' . $index);
        if ($search) {
            $field = $action->getArgument('mDataProp_' . $index);
            if ($field == "name") {
                $filterQuery.= sprintf(" and paramv.name ~* '%s'", pg_escape_string($search));
            }
        }
        $filter++;
    }
    
    $withStatic = ($withStatic == "true") ? "" : "and kind!='static' and kind!='readonly'";
    
    if ($pview == "alluser") {
        if ($userid != "") simpleQuery($action->dbaccess, sprintf("select paramv.*, paramdef.descr, paramdef.kind, application.name as appname from paramv,paramdef,application,application as a where paramv.name=paramdef.name and ((paramv.appid=paramdef.appid and a.id=application.id) or (paramv.appid=application.id and application.childof=a.name and a.id=paramdef.appid)) and paramv.name != 'APPNAME' and paramv.name != 'INIT' and paramv.name!= 'VERSION' and paramdef.isuser='Y' and type='%s' %s and application.id=paramv.appid and (application.tag%sE'\\\\ySYSTEM\\\\y' %s) %s order by application.name, paramv.name, paramv.type desc", pg_escape_string(Param::PARAM_USER . $userid) , $withStatic, $type, $second_type, $filterQuery) , $userParams);
        $paramType.= "and paramdef.isuser='Y'";
    }
    
    if ($pview != "alluser" || $userid) {
        /**
         * Getting all parameters
         */
        simpleQuery($action->dbaccess, sprintf("SELECT paramv.*, paramdef.descr, paramdef.kind, application.name as appname from paramv,paramdef,application,application as a where paramv.name=paramdef.name and ((paramv.appid=paramdef.appid and a.id=application.id) or (paramv.appid=application.id and application.childof=a.name and a.id=paramdef.appid)) and paramv.name != 'APPNAME' and paramv.name != 'INIT' and paramv.name!= 'VERSION' and  ((type = '%s') OR (type='%s')) %s and application.id=paramv.appid and (application.tag%sE'\\\\ySYSTEM\\\\y' %s) %s %s order by application.name, paramv.name, paramv.type desc", Param::PARAM_GLB, Param::PARAM_APP, $withStatic, $type, $second_type, $filterQuery, $paramType) , $tparam);
    }
    
    $vsection = "appid";
    
    $precApp = 0;
    $data = array();
    $appName = "";
    foreach ($tparam as $v) {
        if (isset($v[$vsection])) {
            $tincparam = array();
            if ($v[$vsection] != $precApp) {
                $precApp = $v[$vsection];
                $appinc = array(
                    "name" => "",
                    "val" => ""
                );
                
                $app1 = new Application($action->dbaccess, $precApp);
                $appinc["appname"] = $app1->name;
                $appinc["appicon"] = $action->parent->getImageLink($app1->icon, true, 25);
                $appinc["descr"] = $action->text($app1->short_name);
                $appinc["DT_RowClass"] = "appHeader";
                $appinc["DT_RowId"] = $app1->name;
                $data[] = $appinc;
                $appName = $app1->name;
                $appid = $app1->id;
            }
            if ($pview == "alluser" && !empty($userParams)) {
                foreach ($userParams as $uparams) {
                    if ($uparams["name"] === $v["name"] && $uparams["appid"] === $v["appid"]) {
                        $tincparam = $uparams;
                        break;
                    }
                }
            }
            if (empty($tincparam)) {
                $tincparam = $v;
            }
            // to show difference between global, user and application parameters
            if ($tincparam["type"][0] == Param::PARAM_APP) $tincparam["classtype"] = "aparam";
            else if ($tincparam["type"][0] == Param::PARAM_USER) $tincparam["classtype"] = "uparam";
            else $tincparam["classtype"] = "gparam";
            if ($tincparam["kind"] == "password") {
                if ($tincparam["val"] != "") $tincparam["val"] = "*****";
            }
            $tincparam["sval"] = str_replace(array(
                '"'
            ) , array(
                "&quot;"
            ) , $tincparam["val"]);
            
            $tincparam["colorstatic"] = ($tincparam["kind"] == "static" || $tincparam["kind"] == "readonly") ? "#666666" : "";
            if ($tincparam["kind"] == "static" || $tincparam["kind"] == "readonly") {
                $tincparam["classtype"].= " static";
            }
            // force type user if user mode
            if ($userid > 0) $tincparam["type"] = Param::PARAM_USER . $userid;
            
            if ($tincparam["descr"] == "") $tincparam["descr"] = $tincparam["name"];
            else $tincparam["descr"] = _($tincparam["descr"]);
            $tincparam["tooltip"] = $tincparam["name"] . " : " . $tincparam["descr"];
            $tincparam["appname"] = $appName;
            $tincparam["DT_RowId"] = $tincparam["name"] . $appid;
            $data[] = $tincparam;
        }
    }
    $out["aaData"] = $data;
    $out["iTotalRecords"] = count($data);
    $out["iTotalDisplayRecords"] = $out["iTotalRecords"];
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}

function appmngGetAppsParam(Action & $action)
{
    $filterName = $action->getArgument("filterName");
    $pview = $action->getArgument("pview"); // set to "all" or "single" if user parameters
    $type = $action->getArgument("type");
    $withstatic = $action->getArgument("withstatic");
    
    $applist = array();
    $tab = array();
    $cond = "";
    if ($filterName) {
        $cond = sprintf(" and (lower(application.name) ~'%s')", pg_escape_string(mb_strtolower($filterName)));
    }
    if ($pview === "alluser") {
        $cond.= " and paramdef.isuser='Y'";
    }
    if ($withstatic != "true") {
        $cond.= " and kind!='static' and kind!='readonly'";
    }
    $system = "~";
    $null = "";
    if ($type !== "system") {
        $system = "!~";
        $null = "or application.tag IS NULL";
    }
    simpleQuery($action->dbaccess, sprintf("select DISTINCT application.name, application.id, application.icon from paramv,paramdef,application where paramv.name = paramdef.name and paramv.name != 'APPNAME' and paramv.name != 'INIT' and paramv.name!= 'VERSION' and application.id=paramv.appid and (application.tag%sE'\\\\ySYSTEM\\\\y' %s) %s order by application.name asc", $system, $null, $cond) , $applist);
    
    foreach ($applist as $v) {
        $tab[] = array(
            "label" => $v["name"],
            "value" => $v["id"],
            "img" => '<img title="' . $v["name"] . '" src="' . $action->parent->getImageLink($v["icon"], true, 18) . '" />'
        );
    }
    
    if ((count($tab) == 0) && ($filterName != '')) {
        $tab[] = array(
            "label" => sprintf(_("appmng:no application match '%s'") , $filterName) ,
            "value" => 0
        );
    } else {
        $tab[] = array(
            "label" => _("Select all application") ,
            "value" => "",
            "img" => '<img title="' . _("all applications") . '" src="' . $action->parent->getImageLink("appmng.png", true, 18) . '" class="ui-icon-empty"/>'
        );
    }
    
    $action->lay->template = json_encode($tab);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
    return $tab;
}

function cmpappid($a, $b)
{
    if ($a["appid"] == $b["appid"]) return 0;
    if ($a["appid"] > $b["appid"]) return 1;
    return -1;
}

function cmpappname($a, $b)
{
    if ($a["appname"] == $b["appname"]) return 0;
    if ($a["appname"] > $b["appname"]) return 1;
    return -1;
}
