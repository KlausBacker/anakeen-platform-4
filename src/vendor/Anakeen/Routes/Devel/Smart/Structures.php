<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Router\ApiV2Response;

/**
 * Get All Structures
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/vendor/
 * @note Used by route : GET /api/v2/devel/smart/structures/all/
 */
class Structures
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
        $s=new \SearchDoc("", -1);
        if ($this->target === "vendor") {
            $s->addFilter("atags is null or atags->>'vendor' <> 'Anakeen'");
        }
        $s->setOrder("name, id");

        $this->filterRequest($s);
        $s->setObjectReturn();
        $dl=$s->search()->getDocumentList();
        $structData=[];
        foreach ($dl as $structure) {
            $structData[]=[
                "id"=>intval($structure->id),
                "name"=>$structure->name,
                "title"=>$structure->getTitle(),
                "icon"=>$structure->getIcon("", 32)
            ];
        }
        $data=$structData;
        return $data;
    }
}
