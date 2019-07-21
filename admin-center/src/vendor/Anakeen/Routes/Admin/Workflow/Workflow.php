<?php


namespace Anakeen\Routes\Admin\Workflow;

use Anakeen\SmartElementManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;

class Workflow
{
    protected $filters;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $data = $this->doRequest();
        $result = [];
        if (!empty($this->filters[0]["value"])) {
            foreach ($data as $datum) {
                if (strpos(strtolower($datum["title"]), strtolower($this->filters[0]["value"])) !== false) {
                    array_push($result, $datum);
                }
            }
        } else {
            $result = $data;
        }
        return ApiV2Response::withData($response, $result);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        if ($request->getQueryParam("filter") && isset($request->getQueryParam("filter")["filters"])) {
            $this->filters = $request->getQueryParam("filter")["filters"];
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
            $structure = SmartElementManager::getFamily($wdoc->getAttributeValue("wf_famid"));
            $wfData = [
                "id" => $wdoc->name ?: $wdoc->id,
                "title" => $wdoc->title,
                "baTitle" => $wdoc->getAttributeValue("ba_title"),
                "icon" => $wdoc->getIcon("", 32),
                "wfDesc" => $wdoc->getAttributeValue("wf_desc"),
                "wfFamid" => $wdoc->getAttributeValue("wf_famid"),
                "wfFam" => $wdoc->getAttributeValue("wf_fam"),
                "dpdocFamid" => $wdoc->getAttributeValue("dpdoc_famid"),
                "dpdocFam" => $wdoc->getAttributeValue("dpdoc_fam")
            ];
            if ($structure) {
                $wfData = array_merge($wfData, [
                    "ssId" => intval($structure->id),
                    "ssName" => $structure->name,
                    "ssTitle" => $structure->getTitle(),
                    "ssIcon" => $structure->getIcon("", 32),
                ]);
            }
            $data[] = $wfData;
        }
        return $data;
    }
}
