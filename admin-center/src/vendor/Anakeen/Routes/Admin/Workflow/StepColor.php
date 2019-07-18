<?php


namespace Anakeen\Routes\Admin\Workflow;

use Anakeen\Router\ApiV2Response;

class StepColor extends WorkflowData
{

    protected $stepId;
    protected $color;
    protected $result;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $this->workflow->setStateColor($this->stepId, $this->color);
        $this->workflow->store();
        return ApiV2Response::withData($response, "");
    }

    public function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $this->stepId = $args["step"];
        $data = $request->getParsedBody();
        $this->color = $data["color"];
    }
}