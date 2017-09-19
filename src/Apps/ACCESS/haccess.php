<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once "FDL/freedom_util.php";
/**
 * return account documents
 * @param Action $action
 * @internal param string $filterName title filter key
 * @internal param int $limit max account returned
 * @return array
 */
function accessGetAccounts(Action & $action)
{
    $limit = 25;
    $accountType = $action->getArgument("accountType");
    $filterName = $action->getArgument("filterName");
    
    $condaTs = GetSqlCond(explode('|', $accountType) , "accounttype");
    $condaTs.= " and id != 1";
    if ($filterName) {
        $name = pg_escape_string(mb_strtolower($filterName));
        $cond = sprintf(" and (login~'%s' or lower(lastname) ~ '%s' or lower(firstname) ~ '%s')", $name, $name, $name);
        $condaTs.= $cond;
    }
    $sql = sprintf("select id, login, firstname, lastname, accounttype from users where $condaTs order by lastname LIMIT $limit");
    
    simpleQuery($action->dbaccess, $sql, $result);
    $t = array();
    
    foreach ($result as $aAccount) {
        if ($aAccount["accounttype"] == 'U') {
            $dn = trim(sprintf("%s %s (%s)", htmlspecialchars($aAccount["lastname"]) , htmlspecialchars($aAccount["firstname"]) , htmlspecialchars($aAccount["login"])));
        } else {
            $dn = trim(sprintf("%s %s", htmlspecialchars($aAccount["lastname"]) , htmlspecialchars($aAccount["firstname"])));
        }
        $t[] = array(
            "label" => $dn,
            "value" => $aAccount["id"]
        );
    }
    if ((count($t) == 0) && ($filterName != '')) $t[] = array(
        "label" => sprintf(_("access:no account match '%s'") , $filterName) ,
        "value" => 0
    );
    $action->lay->template = json_encode($t);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
    return $t;
}

function accessGetApps(Action & $action)
{
    $limit = 20;
    $filterName = $action->getArgument("filterName");
    $condaTs = "app.id = acl.id_application";
    
    if ($filterName) {
        $name = pg_escape_string(mb_strtolower($filterName));
        $cond = sprintf(" and (lower(app.name) ~'%s' or lower(app.short_name) ~ '%s' )", $name, $name);
        $condaTs.= $cond;
    }
    $sql = sprintf("select app.id, app.name, app.short_name from application as app, acl as acl WHERE $condaTs group by app.id, app.name, app.short_name order by app.name LIMIT $limit");
    
    simpleQuery($action->dbaccess, $sql, $result);
    $t = array();
    foreach ($result as $aApp) {
        $dn = trim(sprintf("%s (%s)", htmlspecialchars($aApp["name"]) , htmlspecialchars($action->text($aApp['short_name']))));
        $t[] = array(
            "label" => $dn,
            "value" => $aApp["id"]
        );
    }
    if ((count($t) == 0) && ($filterName != '')) $t[] = array(
        "label" => sprintf(_("access:no application match '%s'") , $filterName) ,
        "value" => 0
    );
    $action->lay->template = json_encode($t);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
    return $t;
}

function accessGetDatatableInfo(Action & $action)
{
    $filter = 0;
    $start = intval($action->getArgument('iDisplayStart'));
    $limit = intval($action->getArgument('iDisplayLength'));
    $data = array();
    $sEcho = intval($action->getArgument('sEcho'));
    $out = array(
        "sEcho" => $sEcho
    );
    
    $standurl = $action->GetParam("CORE_STANDURL");
    // select the first user if not set
    $appl_id = trim($action->getArgument("app_id"));
    
    $appl_sel = new Application($action->dbaccess, $appl_id);
    // display application / object class
    if (!$appl_sel->isAffected()) {
        $out["errors"] = sprintf("application %s not found", $appl_id);
        $out["iTotalRecords"] = 0;
    } else {
        $jsscript = "displaySubWindow(330, 500, '" . $standurl . "app=ACCESS&action=EDIT&mod=app&isclass=no&id=[id]&access_appl_id=" . $appl_sel->id . "', '" . _("controlaccess") . "', appDatatable)";
        // Init a querygen object to select users
        $query = new QueryDb($action->dbaccess, "Account");
        //
        // 1) Get all users except admin
        $query->AddQuery("id != 1");
        $sortSearch = '';
        $sortArg = $action->getArgument(sprintf("iSortCol_%d", 0) , null);
        if ($sortArg !== null) {
            if ($sortArg === "2") {
                $sortSearch = "firstname, lastname";
            } else if ($sortArg === "0") {
                $sortSearch = "accounttype";
            }
            $sortArg = $action->getArgument(sprintf("sSortDir_%d", 0));
            if ($sortArg) {
                $query->desc = $sortArg == "desc" ? "up" : "";
            }
        }
        if ($sortSearch) {
            $query->order_by = $sortSearch;
        } else {
            $query->order_by = 'login';
        }
        for ($index = 0; $index < $action->getArgument('iColumns'); $index++) {
            $search = $action->getArgument('sSearch_' . $index);
            if ($search) {
                $field = $action->getArgument('mDataProp_' . $index);
                if ($field == "name") {
                    $query->AddQuery(sprintf("%s ~* '%s'", "login", pg_escape_string($search)));
                } else if ($field == "description") {
                    $query->AddQuery(sprintf("coalesce(%s,'')||' '||coalesce(%s,'') ~* '%s'", "firstname", "lastname", pg_escape_string($search)));
                } else if ($field == "imgaccess") {
                    $query->AddQuery(sprintf("%s = '%s'", "accounttype", pg_escape_string($search)));
                }
                $filter++;
            }
        }
        
        $accounts = $query->Query($start, $limit, "TABLE");
        if ($accounts) {
            
            $accountttypes = getAccounttypesImage($action);
            
            foreach ($accounts as $v) {
                if (!isset($v["login"])) continue;
                $currentRow = array();
                $uperm = new Permission($action->dbaccess, array(
                    $v["id"],
                    $appl_sel->id
                ));
                
                $tab = "";
                $aclids = $uperm->privileges;
                if (!$aclids) { // no privilege
                    $aclids = array(
                        0
                    );
                }
                foreach ($aclids as $v2) {
                    if ($v2 == 0) {
                        $tab.= _("none");
                    } else {
                        $acl = new Acl($action->dbaccess, $v2);
                        if ($tab != "" && $acl->name) $tab.= ", ";
                        $tab.= $acl->name;
                    }
                }
                $currentRow["aclname"] = $tab;
                $currentRow["name"] = htmlspecialchars($v["login"]);
                
                $currentRow["selname"] = $v["id"];
                $currentRow["id"] = $v["id"];
                
                if (!isset($v["firstname"])) $v["firstname"] = "";
                if (!isset($v["lastname"])) $v["lastname"] = "";
                $currentRow["description"] = htmlspecialchars($v["firstname"] . " " . $v["lastname"]);
                $currentRow["edit"] = str_replace("[id]", $v["id"], $jsscript);
                foreach ($accountttypes as $accountttype) {
                    if ($accountttype["value"] == $v["accounttype"]) {
                        $currentRow["imgaccess"] = $accountttype["imgsrc"];
                    }
                }
                $data[] = $currentRow;
            }
            $out["iTotalRecords"] = intval($query->Count());
        }
    }
    //Adding and sending info
    if ($filter == 0) $out["iTotalDisplayRecords"] = $out["iTotalRecords"];
    else $out["iTotalDisplayRecords"] = $action->getArgument('totalSearch');
    $out['aaData'] = $data;
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}

function accessUserGetDatatableInfo(Action & $action)
{
    $accountType = $action->getArgument("accountType", "U");
    $filter = 0;
    $start = intval($action->getArgument('iDisplayStart'));
    $data = array();
    $sEcho = intval($action->getArgument('sEcho'));
    $out = array(
        "sEcho" => $sEcho
    );
    $standurl = $action->GetParam("CORE_STANDURL");
    $user_id = trim($action->getArgument("user_id"));
    $user_sel = new Account();
    $user_sel->Select($user_id);
    if (!$user_sel->id) {
        $out["errors"] = sprintf("account %s not found", $user_id);
        $out["iTotalRecords"] = 0;
    } else {
        if ($accountType == "G") {
            $varreg = "access_group_id";
        } elseif ($accountType == "R") {
            $varreg = "access_role_id";
        } else {
            $varreg = "access_user_id";
        }
        $jsscript = "displaySubWindow(330, 500, '" . $standurl . "app=ACCESS&action=EDIT&mod=user&accountType=$accountType&id=[id]&$varreg=" . $user_sel->id . "', '" . _("controlaccess") . "', urgDatatable)";
        // 1) Get all application
        $tab = array();
        
        $sortSearch = '';
        $orderBy = "";
        $where = "";
        $sortArg = $action->getArgument(sprintf("iSortCol_%d", 0) , null);
        if ($sortArg !== null) {
            if ($sortArg === "1") {
                $sortSearch = "app.description";
            }
            $orderBy = $action->getArgument(sprintf("sSortDir_%d", 0));
        }
        if ($sortSearch) {
            $orderBy = $sortSearch . " " . $orderBy;
        } else {
            $orderBy = 'app.name' . " " . $orderBy;
        }
        for ($index = 0; $index < $action->getArgument('iColumns'); $index++) {
            $search = $action->getArgument('sSearch_' . $index);
            if ($search) {
                $field = $action->getArgument('mDataProp_' . $index);
                if ($field == "name") {
                    $where.= sprintf("and %s ~* '%s'", "app.name", pg_escape_string($search));
                }
                $filter++;
            }
        }
        $start = $start ? "offset " . $start : "";
        simpleQuery($action->dbaccess, sprintf("select distinct app.* from application as app, acl as acl WHERE app.id = acl.id_application %s order by %s %s", $where, $orderBy, $start) , $tab);
        // 2) Get all acl for all application
        foreach ($tab as $v) {
            if (!isset($v["id"])) continue;
            $currentRow = array();
            // get user permissions
            $uperm = new Permission($action->dbaccess, array(
                $user_sel->id,
                $v["id"]
            ));
            
            $acltab = "";
            
            $aclids = $uperm->privileges;
            if (!$aclids) { // no privilege
                $aclids = array(
                    0
                );
            }
            foreach ($aclids as $v2) {
                if ($v2 == 0) {
                    $acltab.= _("none");
                } else {
                    $acl = new Acl($action->dbaccess, $v2);
                    if ($acltab != "" && $acl->name) $acltab.= ", ";
                    $acltab.= $acl->name;
                }
            }
            $currentRow["aclname"] = $acltab;
            $currentRow["name"] = htmlspecialchars($v["name"]);
            
            $currentRow["selname"] = htmlspecialchars($v["name"]);
            $currentRow["id"] = $v["id"];
            $currentRow["description"] = htmlspecialchars($action->text($v['description']));
            $currentRow["edit"] = str_replace("[id]", $v["id"], $jsscript);
            $currentRow["imgaccess"] = $action->parent->getImageLink($v["icon"], true, 18);
            $data[] = $currentRow;
        }
        $out["iTotalRecords"] = count($tab);
    }
    //Adding and sending info
    if ($filter == 0) $out["iTotalDisplayRecords"] = $out["iTotalRecords"];
    else $out["iTotalDisplayRecords"] = $action->getArgument('totalSearch');
    $out['aaData'] = $data;
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}

function getAccounttypesImage(Action & $action)
{
    $dr = new_doc($action->dbaccess, "ROLE");
    $du = new_doc($action->dbaccess, "IUSER");
    $dg = new_doc($action->dbaccess, "IGROUP");
    return array(
        array(
            "value" => "U",
            "imgsrc" => $du->getIcon('', 18) ,
            "label" => $du->getTitle()
        ) ,
        array(
            "value" => "G",
            "imgsrc" => $dg->getIcon('', 18) ,
            "label" => $dg->getTitle()
        ) ,
        array(
            "value" => "R",
            "imgsrc" => $dr->getIcon('', 18) ,
            "label" => $dr->getTitle()
        ) ,
        array(
            "value" => "",
            "imgsrc" => $action->parent->getImageLink("access.gif", true, 18) ,
            "label" => _("access:All") ,
            "imgclass" => "ui-icon ui-icon-radio-on"
        )
    );
}

function accessGetAccounttypesImage(Action & $action)
{
    $out = getAccounttypesImage($action);
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
