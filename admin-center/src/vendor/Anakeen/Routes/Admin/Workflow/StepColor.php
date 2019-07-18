<?php


namespace Anakeen\Routes\Admin\Workflow;

use Anakeen\Router\ApiV2Response;

class StepColor extends WorkflowData
{

    protected $stepId;
    protected $color;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $steps = $this->workflow->getStates();
        foreach ($steps as $step) {
            if ($step === $this->stepId) {
                $this->workflow->setStateColor($step, $this->color);
            }
        }

        return ApiV2Response::withData($response,"");
    }

    public function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $this->stepId = $args["stepId"];
        $data = $request->getParsedBody();
        $this->color = $data["color"];
    }
}