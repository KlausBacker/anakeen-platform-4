<?php

function param_ulist(Action & $action)
{
    
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
            "src" => $action->parent->getJsLink($action->GetParam("CORE_JSURL") . "/subwindow.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink($action->GetParam("CORE_JSURL") . "/logmsg.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink($action->GetParam("CORE_JSURL") . "/AnchorPosition.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink($action->GetParam("CORE_JSURL") . "/PopupWindow.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink($action->GetParam("CORE_JSURL") . "/ColorPicker2.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink($action->GetParam("CORE_JSURL") . "/OptionPicker.js")
        ) ,
        array(
            "src" => $action->parent->getJsLink("APPMNG:appmng.js", true)
        ) ,
        array(
            "src" => $action->parent->getJsLink("APPMNG:param_list.js", true)
        )
    );
    
    $action->parent->AddCssRef("css/dcp/jquery-ui.css");
    $action->parent->AddCssRef("legacy/jquery-dataTables/css/jquery.dataTables.css");
    $action->parent->AddCssRef("APPMNG:param_ulist.css", true);
    $action->parent->AddCssRef("APPMNG:appmng.css", true);
    $action->parent->addCssRef("admin/adminAssets/adminReset.css");
    
    $action->lay->setBlockData("JS_LINKS", $jslinks);
    
    return;
}
/**
 * Get user list
 *
 * @param Action $action
 */
function appmngGetUsers(Action & $action)
{
    $usage = new ActionUsage($action);
    
    $filterName = $usage->addOptionalParameter("filterName", "filterName");
    
    $usage->verify(true);
    
    $data = array();
    
    $search = new SearchDoc("", "IUSER");
    $search->setObjectReturn(true);
    $search->addFilter("title ~* '%s' or us_login ~* '%s'", $filterName, $filterName);
    $search->setSlice(25);
    
    foreach ($search->getDocumentList() as $currentUser) {
        /* @var $currentUser Doc */
        $data[] = array(
            "label" => trim(sprintf("%s (%s)", $currentUser->getHTMLTitle() , htmlspecialchars($currentUser->getRawValue("us_login")))) ,
            "value" => $currentUser->getRawValue("us_whatid")
        );
    }
    if ((count($data) == 0) && ($filterName != '')) {
        $data[] = array(
            "label" => sprintf(_("appmng:no account match '%s'") , $filterName) ,
            "value" => 0
        );
    }
    
    $action->lay->template = json_encode($data);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
?>
