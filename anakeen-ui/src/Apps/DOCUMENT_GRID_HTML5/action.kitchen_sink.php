<?php
function kitchen_sink(\Anakeen\Core\Internal\Action &$action)
{
    $action->lay->set("WS", \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION"));


}