<?php

function wrap_kendo(Action &$action)
{

    $etagManager = new \Dcp\Ui\Etags("application/javascript");
    $etag = $etagManager->generateEtag();
    $useCache = $etagManager->verifiyEtag($etag);
    if ($useCache) {
        $etagManager->generateResponseHeader($etag, $useCache);
        $action->lay->template = '';
        $action->lay->noparse = true;
        return;
    }

    $usage = new ActionUsage($action);
    $jsFile = $usage->addRequiredParameter("jsFile", "jsFile");
    $usage->setStrictMode(false);
    $usage->verify();

    $jsFile = str_replace("/", "", $jsFile);
    $jsFile = str_replace("..", "", $jsFile);

    $action->lay->set("KENDO", file_get_contents("lib/KendoUI/js/$jsFile.js"));

    $etagManager->generateResponseHeader($etag, $useCache);
}