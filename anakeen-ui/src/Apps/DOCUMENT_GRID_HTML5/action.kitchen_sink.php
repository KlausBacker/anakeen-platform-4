<?php
function kitchen_sink(Action &$action)
{
    $action->lay->set("WS", \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION"));


}