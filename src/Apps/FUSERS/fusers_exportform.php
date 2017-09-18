<?php
/*
 * @author Anakeen
 * @package FDL
*/

function fusers_exportform(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Get form option to export accounts");
    
    $type = $usage->addOptionalParameter("accountType", "restricted to account type", array(
        "user",
        "role",
        "group"
    ));
    
    $group = $usage->addOptionalParameter("group", "selected group");
    
    $usage->verify();

    $uprefs=json_decode($action->getParam("FUSERS_PORTPREF", "{}"),true);

    $options= array("deepGroup","exportRole","exportPassword","exportGroup","exportSchema","exportDocument");
    foreach ($options as $pref) {
        $action->lay->set("select_$pref", !empty($uprefs[$type][$pref])?"selected":"");
    }

    $action->lay->set("selectGroup", false);
    $action->lay->set("crypt", false);
    $action->lay->set("role", false);
    $action->lay->set("group", false);
    $action->lay->set("statusKey", uniqid("exacct"));
    
    switch ($type) {
        case "user":
            $action->lay->set("selectGroup", !empty($group));
            $action->lay->set("crypt", true);
            $action->lay->set("role", true);
            $action->lay->set("group", true);
            break;

        case "group":
            $action->lay->set("role", true);
            break;

        case "role":
            break;
    }
}
