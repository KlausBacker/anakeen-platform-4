<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class WorkflowState
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/workflows/states/{state}
 * @package Anakeen\Routes\Core
 */
class WorkflowSetTransition extends WorkflowTransition
{
    protected $comment;
    protected $parameters = [];

    /**
     * Change state
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $messages = [];
        $info = $this->doRequest($messages);
        return ApiV2Response::withData($response, $info, $messages);
    }

    protected function doRequest(&$messages = [])
    {
        $this->workflow->disableEditControl();
        if (!empty($this->parameters) && is_array($this->parameters)) {
            foreach ($this->parameters as $aid => $value) {
                $this->workflow->setAttributeValue($aid, $value);
            }
        }
        $this->workflow->enableEditControl();

        $state = $this->getState();
        if (!$state) {
            $exception = new Exception("CRUD0235", $this->workflow->title, $this->workflow->id);
            $exception->setHttpStatus("404", "Invalid transition");
            throw $exception;
        }

        $err = $this->workflow->changeState(
            $state,
            $this->comment,
            $force = false,
            true,
            true,
            true,
            true,
            true,
            true,
            $message
        );
        if ($err) {
            $exception = new Exception("CRUD0230", $err);
            $exception->setHttpStatus("403", "Forbidden");
            $exception->setUserMessage($err);
            throw $exception;
        }
        if ($message) {
            $msg = new ApiMessage();
            $msg->contentText = $message;
            $msg->type = ApiMessage::MESSAGE;
            $msg->code = "WORKFLOW_TRANSITION";
            $messages[] = $msg;
        }

        return parent::doRequest();
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $content = $request->getParsedBody();

        $this->comment = $content["comment"];
        $this->parameters = $content["parameters"];
    }
}
