<?php


namespace Anakeen\Routes\Devel\Workflow;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;

class StructureWorkflow
{
    protected $target="all";
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->target = $args["target"];
        $this->filters = $request->getParam("filter", []);
    }

    protected function filterRequest(\SearchDoc $searchDoc)
    {
        if (!empty($this->filters)) {
            if (!empty($this->filters["logic"]) && !empty($this->filters["filters"])) {
                $filters = $this->filters["filters"];
                $logic = sprintf(" %s ", strtoupper($this->filters["logic"]));
                $filterSql = implode($logic, array_map(function ($filter) {
                    return sprintf("%s ~* '%s'", pg_escape_string($filter["field"]), pg_escape_string($filter["value"]));
                }, $filters));
                $searchDoc->addFilter($filterSql);
            }
        }
    }

    public function doRequest()
    {
        $result = $this->getWflSSList();
        return $result;
    }

    protected function getWflSSList()
    {
        $s = new SearchElements(\SmartStructure\Wdoc::familyName);
        $wdocList = $s->search()->getResults();
        $data = [];
        foreach ($wdocList as $wdoc) {
            $structure = SEManager::getFamily($wdoc->getAttributeValue("wf_famid"));
            $wfData = [
                "id"=> $wdoc->name ?: $wdoc->id,
                "title" => $wdoc->title,
                "baTitle" => $wdoc->getAttributeValue("ba_title"),
                "icon" => $wdoc->getIcon("", 32),
                "wfDesc" => $wdoc->getAttributeValue("wf_desc"),
                "wfFamid" => $wdoc->getAttributeValue("wf_famid"),
                "wfFam" => $wdoc->getAttributeValue("wf_fam"),
                "dpdocFamid" => $wdoc->getAttributeValue("dpdoc_famid"),
                "dpdocFam" => $wdoc->getAttributeValue("dpdoc_fam"),
                "ssId"=>intval($structure->id),
                "ssName"=>$structure->name,
                "ssTitle"=>$structure->getTitle(),
                "ssIcon"=>$structure->getIcon("", 32),
            ];
            $data[] = $wfData;
        }
        return $data;
    }
}
