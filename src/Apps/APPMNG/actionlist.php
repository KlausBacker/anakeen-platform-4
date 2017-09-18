<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display actions paramters
 *
 * @author Anakeen
 * @version $Id: actionlist.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function actionlist(Action & $action)
{
    $packUrl = $action->parent->getJsLink("APPMNG:appmng.js", true, "APPMNGACTION");
    $action->parent->getJsLink("APPMNG:actionlist.js", true, "APPMNGACTION");
    $jslinks = array(
        array(
            "src" => $action->parent->getJsLink("legacy/jquery/jquery.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink("legacy/jquery-ui/js/jquery-ui.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink("legacy/jquery-dataTables/js/jquery.dataTables.js")
        ) ,
        array(
            "src" => $packUrl
        )
    );
    
    $action->parent->AddCssRef("css/dcp/jquery-ui.css");
    $action->parent->AddCssRef("legacy/jquery-dataTables/css/jquery.dataTables.css");
    $action->parent->AddCssRef("APPMNG:appmng.css", true);
    $action->parent->addCssRef("admin/adminAssets/adminReset.css");
    
    $action->lay->setBlockData("JS_LINKS", $jslinks);
    $appl_info = array();
    simpleQuery($action->dbaccess, "select id,name from application where name='ACCESS'", $appl_info, false, true);
    $action->lay->set("id", $appl_info["name"]);
    $action->lay->set("action_appl_id", $appl_info["id"]);
}

function appmngGetApps(Action & $action)
{
    $filterName = $action->getArgument("filterName");
    $applist = array();
    $condaTs = "";
    
    if ($filterName) {
        $name = pg_escape_string(mb_strtolower($filterName));
        $cond = sprintf("WHERE (lower(name) ~'%s')", $name);
        $condaTs.= $cond;
    }
    simpleQuery($action->dbaccess, sprintf("select id,name,icon from application $condaTs order by name") , $applist);
    $tab = array();
    foreach ($applist as $v) {
        $tab[] = array(
            "label" => htmlspecialchars($v["name"]) ,
            "value" => $v["id"],
            "img" => '<img title="' . $v["name"] . '" src="' . $action->parent->getImageLink($v["icon"], true, 18) . '" />'
        );
    }
    if ((count($tab) == 0) && ($filterName != '')) $tab[] = array(
        "label" => sprintf(_("appmng:no application match '%s'") , $filterName) ,
        "value" => 0
    );
    $action->lay->template = json_encode($tab);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
    return $tab;
}

function appmngGetDatatableInfo(Action & $action)
{
    $sEcho = intval($action->getArgument('sEcho'));
    $out = array(
        "sEcho" => $sEcho
    );
    $data = array();
    $appl_id = intval($action->getArgument("action_appl_id"));
    simpleQuery($action->dbaccess, sprintf("select * from action where id_application=%s order by name", pg_escape_string($appl_id)) , $data);
    
    foreach ($data as $k => $v) {
        $data[$k]["name"] = htmlspecialchars($v["name"]);
        $data[$k]["short_name"] = htmlspecialchars($action->text($v['short_name']));
    }
    $out["aaData"] = $data;
    $out["iTotalRecords"] = count($data);
    $out["iTotalDisplayRecords"] = $out["iTotalRecords"];
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
