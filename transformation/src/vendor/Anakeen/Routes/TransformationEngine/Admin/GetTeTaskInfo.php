<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\TransformationEngine\Client;

/**
 *
 * @use     by route GET /api/admin/transformationengine/tasks/{task}
 */
class GetTeTaskInfo
{

    protected $taskId;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->taskId = $args["task"];
    }

    /**
     *
     * @return array
     */
    protected function doRequest()
    {
        return $this->getTetaskInfo();
    }

    public function getTetaskInfo()
    {

        $te = new Client();
        $te->getInfo($this->taskId, $info);


        if (! $info) {
            $e=new Exception("Not found");
            $e->setHttpStatus(404, "Task not found");
            $e->setUserMessage(sprintf("task \"%s\" not exists\"", $this->taskId));
            throw $e;
        }

        $te->retrieveTaskHisto($this->taskId, $histo);


        return ["info" => $info, "histo" => $histo];
    }
}
