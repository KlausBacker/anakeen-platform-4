<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Router\ApiV2Response;
use Anakeen\TransformationEngine\Client;
use Anakeen\Ui\DataSource;

/**
 *
 * @use     by route GET /api/admin/transformationengine/tasks/
 */
class GetTeTasks
{

    protected $start=0;
    protected $slice = -1;
    protected $filters=[];

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->slice = intval($request->getQueryParam("take"));
        $this->start = intval($request->getQueryParam("skip"));

        $filters = $request->getQueryParam("filter");
        if ($filters) {
            $requestFilters = DataSource::getFlatLevelFilters($filters);
            foreach ($requestFilters as $requestFilter) {
                $field=$requestFilter["field"];
                switch ($field) {
                    case "tid":
                    case "cdate":
                    case "engine":
                    case "status":
                        $this->filters[$field]=sprintf("%s", pg_escape_string($requestFilter["value"]));
                        break;
                }
            }
        }
    }

    /**
     *
     * @return array
     */
    protected function doRequest()
    {
        $tasksInfo = $this->getTetask();
        $data["tasks"] = $tasksInfo["tasks"];
        if ($this->filters) {
            $data["total"] = intval($tasksInfo["count_filter"]);
        } else {
            $data["total"] = intval($tasksInfo["count_all"]);
        }
        return $data;
    }

    public function getTetask()
    {
        $te = new Client();
        $te->retrieveTasks($task, $this->start, $this->slice, "cdate", "desc", $this->filters);

        return $task;
    }
}
