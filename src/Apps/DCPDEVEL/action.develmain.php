<?php
function develmain(Action & $action)
{
    $action->parent->addJsRef("legacy/jquery-ui-1.12.0/external/jquery/jquery.js");
    $action->parent->addJsRef("legacy/jquery-ui-1.12.0/jquery-ui.js");
    $action->parent->addJsRef("legacy/jquery-dataTables/1.10/js/jquery.dataTables.js");
    $action->parent->addJsRef("DCPDEVEL/Layout/develmain.js");

    $action->parent->addCssRef("DCPDEVEL/Layout/develinit.css");
    $action->parent->addCssRef("DCPDEVEL/Layout/develmain.css");
    $action->parent->addCssRef("legacy/jquery-ui-1.12.0/jquery-ui.css");
    //  $action->parent->addCssRef("legacy/jquery-ui-1.12.0/jquery-ui.structure.css");
    //  $action->parent->addCssRef("legacy/jquery-ui-1.12.0/jquery-ui.theme.min.css");
    $action->parent->addCssRef("legacy/jquery-dataTables/1.10/css/jquery.dataTables.css");
    $action->parent->addCssRef("legacy/jquery-dataTables/1.10/css/dataTables.jqueryui.css");
    
    $action->parent->addCssRef("DCPDEVEL/Layout/develmain.css");
}
