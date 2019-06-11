<?php
/** @noinspection PhpUnusedParameterInspection */

namespace Anakeen\SmartStructures\Task\Routes;

use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;

/**
 *
 * @note    Used by route : PUT /api/v2/admin/task/{task}
 */
class TaskExecute
{
    /**
     * @var \SmartStructure\Task
     */
    protected $task;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $this->initParameter($request, $args);

        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameter(\Slim\Http\request $request, $args)
    {
        $taskId = $args["task"];
        $this->task = SmartElementManager::getDocument($taskId);
        if ($this->task === null) {
            throw new Exception("Task $taskId not found");
        }
    }

    protected function doRequest()
    {
        $data["properties"] = [
            "title" => $this->task->getTitle(),
            "revision" => $this->task->revision,
            "initid" => $this->task->initid
        ];

        $return = $this->task->execute();
        if ($return === 0) {
            $data["message"] = sprintf(___("Task \"%s\" is executed.", "smart-task"), $this->task->getTitle());
        }

        return $data;
    }
}
