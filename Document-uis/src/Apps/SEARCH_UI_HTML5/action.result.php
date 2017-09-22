<?php

require_once 'FDL/freedom_util.php';

use Dcp\HttpApi\V1\Crud\Exception;

function main(Action &$action){

    $arrayOptions = array();

    $usage = new ActionUsage($action);
    $id = $usage->addRequiredParameter("id", "search or report id");

    $usage->setStrictMode(false);
    $usage->verify(true);

    $doc = \Dcp\HttpApi\V1\DocManager\DocManager::getDocument($id);

    $idFamily = $doc->getAttributeValue("se_famid");
    $fdoc = \Dcp\HttpApi\V1\DocManager\DocManager::getFamily($idFamily);
    if (!$fdoc) {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("404","Family not found");
        throw $exception;
    }



    $arrayOptions=\Dcp\Search\html5\searchGridAttributes::getGridAttributes($doc);

    $action->lay->set("TITLE", $doc->getTitle());
    $action->lay->set("famid", $idFamily);
    $action->lay->set("id", $id);
    $action->lay->setBlockData("abstract", $arrayOptions);
    $action->lay->set("WS", \ApplicationParameterManager::getParameterValue("CORE", "WVERSION"));
}

   