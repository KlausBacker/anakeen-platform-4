<?php
namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Router\ApiV2Response;

class Workflows
{

    protected $filters = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
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
        $s=new \SearchDoc("", "WDOC");
        $s->setOrder("name, id");

        $this->filterRequest($s);
        $s->setObjectReturn();
        $dl=$s->search()->getDocumentList();
        $workflowData=[];
        foreach ($dl as $workflow) {
            $workflowData[]=[
                "id"=>intval($workflow->id),
                "name"=>$workflow->name,
                "title"=>$workflow->getTitle(),
                "icon"=>$workflow->getIcon("", 32),
                "states" => $workflow->getState()
            ];
        }
        $data=$workflowData;
        return $data;
    }
}
