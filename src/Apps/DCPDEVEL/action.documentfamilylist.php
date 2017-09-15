<?php
require_once("FDL/Class.Doc.php");
function documentfamilylist(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("List document family");
    $familyId = $usage->addRequiredParameter("family", "Family identifier");
    $usage->setStrictMode(false);
    $usage->verify();
    /**
     * @var DocFam $family
     */
    $family = new_Doc("", $familyId);
    if (!$family->isAffected()) {
        $action->exitError("Undefined family");
    }
    $action->parent->addJsRef("legacy/jquery-ui-1.12.0/external/jquery/jquery.js");
    $action->parent->addJsRef("legacy/jquery-ui-1.12.0/jquery-ui.js");
    $action->parent->addJsRef("uiAssets/externals/mustache.js/mustache.min.js");
    // $action->parent->addJsRef("legacy/jquery-dataTables/1.10/js/jquery.dataTables.js");
    // $action->parent->addJsRef("DCPDEVEL/Layout/combobox.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/documentfamily.js");

    $action->parent->addCssRef("DCPDEVEL/Layout/develinit.css");
    $action->parent->addCssRef("legacy/jquery-ui-1.12.0/jquery-ui.css");
    $action->parent->addCssRef("legacy/jquery-ui-1.12.0/jquery-ui.structure.css");
    $action->parent->addCssRef("legacy/jquery-ui-1.12.0/jquery-ui.theme.min.css");
    //$action->parent->addCssRef("legacy/jquery-dataTables/1.10/css/jquery.dataTables.css");
    //$action->parent->addCssRef("legacy/jquery-dataTables/1.10/css/dataTables.jqueryui.css");
    $action->parent->addCssRef("DCPDEVEL/Layout/documentfamily.css");
    
    $action->lay->set("famid", $family->id);
    $action->lay->set("famname", $family->name);
    
    $action->lay->set("csvcomma", $action->getParam("CSV_SEPARATOR") === ",");
    $action->lay->set("csvquote", $action->getParam("CSV_ENCLOSURE") === "'");
}
