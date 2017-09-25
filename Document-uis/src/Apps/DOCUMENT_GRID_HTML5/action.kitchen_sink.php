<?php
function kitchen_sink(Action &$action)
{
    $action->lay->set("WS", \ApplicationParameterManager::getParameterValue("CORE", "WVERSION"));


}