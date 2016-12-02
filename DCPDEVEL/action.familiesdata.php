<?php
require_once ("FDL/Class.Doc.php");
function familiesdata(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("List families");
    $search=$usage->addOptionalParameter("search", "search term", function ($a) {
        return is_array($a)?"":"not an array";
    });


    $usage->setStrictMode(false);
    $usage->verify();

    $err="";

    $s=new SearchDoc($action->dbaccess, -1);
    $s->setOrder("name","title");
    $s->setObjectReturn(true);
    if ($search && $search["value"]) {
        $s->addFilter("name ~* '%s' or title ~* '%s'", $search["value"], $search["value"]);
    }

    $dl=$s->search()->getDocumentList();

    $total=$s->count();
    $families=[];
    foreach ($dl as $family) {
        $families[]=array("icon"=>$family->getIcon("", 22),
        "title"=>$family->getTitle(),
        "name"=>$family->name
        );

    }


    $data=["recordsFilters"=>$total, "recordsTotal"=>$total, "data"=>$families];

    header('Content-Type: application/json');
    if ($err) {
        header("HTTP/1.0 400 Error");
        $response = ["success" => false, "error" => $err];
    } else {
        $response = $data;
    }
    $action->lay->noparse = true;
    $action->lay->template = json_encode($response);
}
