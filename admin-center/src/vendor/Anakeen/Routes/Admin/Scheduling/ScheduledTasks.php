<?php

namespace Anakeen\Routes\Admin\Scheduling;

use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElements;
use Anakeen\Ui\DataSource;
use SmartStructure\Task;
use SmartStructure\Fields\Task as TaskFields;

/**
 * Return prevision for tasks
 * @note used by route GET /api/v2/admin/sheduling/tasks/
 */
class ScheduledTasks
{
    protected $start = 0;
    protected $slice = -1;
    protected $filters = [];
    protected $fields = [TaskFields::task_status, TaskFields::task_nextdate, TaskFields::task_exec_state_result];

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->slice = intval($request->getQueryParam("take"));
        $this->start = intval($request->getQueryParam("skip"));

        $filters = $request->getQueryParam("filter");
        if ($filters) {
            $requestFilters = DataSource::getFlatLevelFilters($filters);
            foreach ($requestFilters as $requestFilter) {
                $field = $requestFilter["field"];
                if ($requestFilter["value"]) {
                    switch ($field) {
                        case "title":
                            $this->filters[$field] = sprintf("%s", pg_escape_string($requestFilter["value"]));
                            break;
                    }
                }
            }
        }
    }


    public function doRequest()
    {
        $data = [];

        $s = $this->getSearch();
        $s->setOrder("task_nextdate asc, id");

        $tasks = $s->getResults();

        /** @var Task $task */
        foreach ($tasks as $task) {
            $data["tasks"][] = $this->getTaskData($task);
        }
        $data["total"] = $this->getTotal($s);
        return $data;
    }


    /**
     * @return SearchElements
     * @throws \Anakeen\Search\Exception
     */
    protected function getSearch()
    {
        $s = new SearchElements("TASK");

        if ($this->start) {
            $s->setStart($this->start);
        }
        if ($this->slice > 0) {
            $s->setSlice($this->slice);
        }
        foreach ($this->filters as $field => $value) {
            $s->addFilter("%s ~* '%s'", $field, $value);
        }

        return $s;
    }

    protected function getTotal($s)
    {
        $s->setSlice("ALL");
        $s->setStart(0);
        return $s->onlyCount();
    }

    protected function getTaskData(Task $task)
    {
        $data = [
            "id" => intval($task->id),
            "initid" => intval($task->initid),
            "name" => $task->name,
            "revision" => intval($task->revision),
            "title" => $task->getTitle()
        ];

        foreach ($this->fields as $field) {
            $data[$field] = $task->getRawValue($field);
        }
        return $data;
    }
}
