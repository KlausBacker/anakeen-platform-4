<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\TransformationEngine\Client;

/**
 *
 * @use     by route DELETE /api/admin/transformationengine/tasks/{task}
 */
class DeleteTask
{
    protected $taskId;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);

        $this->doRequest();
        $msg = new ApiMessage(sprintf("Task \"%s\" is deleted", $this->taskId));
        return ApiV2Response::withData($response, [], [$msg]);
    }


    protected function initParameters($args)
    {
        $this->taskId = $args["task"];
    }

    protected function doRequest()
    {
        $this->getTetaskInfo();
    }

    public function getTetaskInfo()
    {
        $te = new Client();
        $err = $te->purgeTransformation($this->taskId);
        if ($err) {
            throw new Exception($err);
        }
    }
}
