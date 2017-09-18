<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Parameters modification
 *
 * @author Anakeen
 * @version $Id: param_mod.php,v 1.10 2006/06/22 12:52:40 eric Exp $
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// -----------------------------------
function param_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $appid = $action->getArgument("appid");
    $name = $action->getArgument("aname");
    $atype = $action->getArgument("atype", Param::PARAM_APP);
    $val = $action->getArgument("val");
    $err = '';
    $data = array();
    $ParamCour = new Param($action->dbaccess, array(
        $name,
        $atype,
        $appid
    ));
    
    $pdef = ParamDef::getParamDef($name,$appid);
    if (!$pdef) {
        $err=sprintf(_("parameter %s not found (application #%d)"),$name, $appid);
    } else {

    if (!$ParamCour->isAffected()) {
        $ParamCour->appid = $appid;
        $ParamCour->type = $atype;
        $ParamCour->name = $name;
        $ParamCour->val = $val;
        $err = $ParamCour->Add();
        if ($err != "") {
            $action->addLogMsg($action->text("err_add_param") . " : $err");
        } else {
            $data["textModify"] = _("param Changed");
        }
    } else {
        if (($pdef->kind == "password") && ($val == '*****')) {
            $data["textModify"] = _("param not changed");
        } else {
            if ($ParamCour->val == $val || $pdef->kind == 'static' || $pdef->kind == 'readonly') {
                if ($ParamCour->val == $val) {
                    $data["textModify"] = _("same value param not changed");
                } else {
                $data["textModify"] = _("read only : param not changed");
                }
            } else {
                $ParamCour->val = $val;
                $err = $ParamCour->Modify();
                if ($err != "") {
                    $action->addLogMsg($action->text("err_mod_parameter") . " : $err");
                } else {
                    $data["textModify"] = _("param Changed");
                }
            }
        }
    }
    if ($pdef->kind == "password") {
        if ($ParamCour->val == '') $data["value"] = $ParamCour->val;
        else $data["value"] = "*****";
    } else {
        $data["value"] = $ParamCour->val;
    }
    }
    $data["id"] = $name;
    $data["appid"] = $ParamCour->appid;
    $action->lay->template = json_encode(array(
        "success" => $err ? false : true,
        "error" => $err,
        "data" => $data
    ));
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
