<?php

namespace Anakeen\Routes\Admin\Scheduling;

use Anakeen\Router\ApiV2Response;
use SmartStructure\Task;
use SmartStructure\Fields\Task as TaskFields;

/**
 * Return executed tasks
 * @note used by route GET /api/v2/admin/sheduling/past-tasks/
 */
class PastTasks extends ScheduledTasks
{

    protected $fields=[TaskFields::task_exec_date,TaskFields::task_exec_duration,TaskFields::task_exec_state_result,TaskFields::task_exec_state_result];
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    public function doRequest()
    {
        $data = [];

        $s = $this->getSearch();

        $s->setDistinct(false);
        $s->setLatest(false);
        $s->addFilter("locked = -1");
        $s->setOrder("task_exec_date desc, id");
        $tasks = $s->getResults();

        /** @var Task $task */
        foreach ($tasks as $task) {
            $data["tasks"][] = $this->getTaskData($task);
        }

        $data["total"] = $this->getTotal($s);
        return $data;
    }
}
