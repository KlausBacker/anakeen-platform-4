<?php

/**
 * send css/js file from Apps//Layout
 *
 * @author Anakeen
 */
function core_asset(Action & $action)
{

    $ref = $action->getArgument("ref");

    $action->lay->template = '';
    $action->lay->noparse = true;
    if (!preg_match("/\\.(css|js)$/", $ref, $reg)) {
        $action->exitError(sprintf("Ref \"%s\" not an asset", $ref));
    }
    $assetType = $reg[1];


    if (preg_match("/([A-Z_0-9-]+):([^:]+):{0,1}[A-Z]{0,1}/", $ref, $reg)) {

        $lfile = getLayoutFile($reg[1], strtolower($reg[2]));
        if (file_exists($lfile)) {
            $action->lay->template = file_get_contents($lfile);
        } else {
            header(sprintf("HTTP/1.1 404 ref [%s] not found", $ref));
            exit;
        }
    } else {
        $action->exitError(sprintf("Ref \"%s\" not an valid reference", $ref));
    }


    switch ($assetType) {
        case "css":
            setHeaderCache("text/css");
            break;
        case "js":
            setHeaderCache("application/javascript");
            break;
    }


}