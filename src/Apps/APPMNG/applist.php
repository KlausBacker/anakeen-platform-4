<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function applist(Action & $action)
{
    $packUrl = $action->parent->getJsLink("APPMNG:appmng.js", true, "APPMNGAPP");
    $action->parent->getJsLink("APPMNG:applist.js", true, "APPMNGAPP");
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
    $action->lay->Set("IMGHELP", $action->parent->getImageLink("help.gif"));
    $action->lay->Set("IMGPRINT", $action->parent->getImageLink("print.gif"));
    $action->lay->Set("IMGEDIT", $action->parent->getImageLink("edit.gif"));
    $action->lay->Set("IMGSEARCH", $action->parent->getImageLink("search.gif"));
    $action->lay->Set("APPLIST", _("Application list"));
}

function appmngGetAppDatatableInfo(Action & $action)
{
    $sEcho = intval($action->getArgument('sEcho'));
    $out = array(
        "sEcho" => $sEcho
    );
    $data = array();
    
    simpleQuery($action->dbaccess, sprintf("select * from application order by name") , $data);
    // Affect the modif icons
    foreach ($data as $k => $v) {
        
        $id = $v["id"];
        $p = new Param($action->dbaccess, array(
            "VERSION",
            Param::PARAM_APP,
            $id
        ));
        $version = (isset($p->val) ? $p->val : "");
        $data[$k]["version"] = $version;
        $data[$k]["description"] = htmlspecialchars($action->text($v['description']));
        $data[$k]["appicon"] = $action->parent->getImageLink($v["icon"], true, 18);
    }
    
    $out["aaData"] = $data;
    $out["iTotalRecords"] = count($data);
    $out["iTotalDisplayRecords"] = $out["iTotalRecords"];
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}

