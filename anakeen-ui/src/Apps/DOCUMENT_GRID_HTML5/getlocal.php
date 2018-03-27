<?php

/**
 * Get the local for the js translate system
 *
 * @param \Anakeen\Core\Internal\Action $action
 */
function getlocal(\Anakeen\Core\Internal\Action &$action) {

    $currentLocale = getParam("CORE_LANG", "fr_FR");

    $currentLocale = strtok($currentLocale, '_');

    $action->lay->template=json_encode($currentLocale);
    $action->lay->noparse=true;
    header('Content-type: application/json');
}

?>
