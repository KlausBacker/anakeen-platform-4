<?php

require_once 'FDL/freedom_util.php';
require_once 'DOCUMENT_GRID_HTML5/getdocgridcontent.php';

/**
 * Return the json content for a docGrid
 *
 * @param Action $action
 */
function export_grid(Action &$action)
{
    $usage = new ActionUsage($action);

    $searchParam = $usage->addRequiredParameter("param", "param");

    $usage->setStrictMode(false);
    $usage->verify(true);

    $searchParam = json_decode($searchParam, true);

    $searchDoc = new SearchDoc("", $searchParam["selectedFam"]);
    $searchDoc->useCollection($searchParam["collection"]);

    $criterias = $searchParam["criterias"];

    $searchCriteria = new \Dcp\DocumentGrid2\SearchCriteria($searchDoc);
    $searchCriteria->addCriterias($criterias);

    $searchDoc = $searchCriteria->getSearchDoc();
    if (isset($searchParam["filters"])) {
        generateFilters($searchDoc, $searchParam["filters"]);
    }

    $sql = $searchDoc->getOriginalQuery();

    $tmpReport = createTmpDoc("", "REPORT");
    /* @var $tmpReport \Dcp\Core\Report */
    $tmpReport->setValue("ba_title", iconv('UTF-8', 'ASCII//TRANSLIT', $searchParam["title"]));
    $tmpReport->setValue("se_famid", $searchParam["selectedFam"]);

    $columns = $searchParam["exportColumns"];

    $columns = array_map(function ($value) {
        return $value["id"];
    }, $columns);

    $tmpReport->setValue("rep_idcols", $columns);

    $tmpReport->store();
    $tmpReport->addStaticQuery($sql);
    $tmpReport->store();

    $return["callback"] = "?app=FDL&action=REPORT_EXPORT_CSV&displayForm=true&id=" . $tmpReport->getPropertyValue("id");

    $action->lay->template = json_encode($return);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}