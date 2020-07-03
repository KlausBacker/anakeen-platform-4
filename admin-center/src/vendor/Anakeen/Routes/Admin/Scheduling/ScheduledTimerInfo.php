<?php

namespace Anakeen\Routes\Admin\Scheduling;

use Anakeen\Core\SEManager;
use Anakeen\Core\TimerTask;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use SmartStructure\Wdoc;

/**
 * Return prevision for specific timer
 * @note used by route GET /api/v2/admin/sheduling/timers/{timerid}
 */
class ScheduledTimerInfo
{
    protected $timerTaskId = 0;
    /**
     * @var \Anakeen\Core\Internal\SmartElement|null
     */
    protected $target;
    /**
     * @var \Anakeen\Core\Internal\SmartElement|null
     */
    protected $timer;
    /**
     * @var string
     */
    protected $abortMessage = "";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->timerTaskId = intval($args["timerid"]);
    }


    public function doRequest()
    {
        $data = [];

        $docTimer = new \DocTimer("", $this->timerTaskId);
        if (!$docTimer->isAffected()) {
            throw new Exception(sprintf("Timer task #%s not exists", $this->timerTaskId));
        }


        $taskInfo = new TimerTask($docTimer->getValues());
        $taskInfo->id = $this->timerTaskId;
        $data["taskInfo"] = $taskInfo;

        if ($taskInfo->donestatus === \DocTimer::expiredStatus) {
            $this->abortMessage = ___("Aborted:", "timer");
        }
        $this->target = SEManager::getDocument($taskInfo->docid);
        $this->timer = SEManager::getDocument($taskInfo->timerid);
        $data["actions"] = $this->getActions($taskInfo);

        return $data;
    }

    protected function getActions(TimerTask $timerTask)
    {
        /*
         *id: 118,
actions: {
state: "",
tmail: [
"1212",
"1338"
],
method: "::getTitle()"
},
timerid: 221197,
originid: 111922,
docid: 112517,
title: "Bill 0003 AMSILI Charles",
fromid: 111918,
attachdate: "2020-07-02 14:09:16",
donedate: null,
donestatus: "waiting",
referencedate: "2020-07-02 14:09:16",
tododate: "2020-07-02 15:09:16",
result: null,
level: "0"
         */
        $origin = SEManager::getDocument($timerTask->originid);
        if ($origin) {
            $actions[] = [
                "date" => $timerTask->attachdate,
                "message" => sprintf(
                    ___("Attach timer \"%s\" by \"%s\"", "timer"),
                    $this->timer->getTitle(),
                    $origin->getTitle()
                )
            ];
        } else {
            $actions[] = [
                "date" => $timerTask->attachdate,
                "message" => sprintf(___("Attach timer \"%s\"", "timer"), $this->timer->getTitle())
            ];
        }


        if (!empty($timerTask->actions["state"])) {
            $actions[] = $this->getWorkflowAction($timerTask);
        }
        if (!empty($timerTask->actions["tmail"])) {
            $actions = array_merge($actions, $this->getMailActions($timerTask));
        }
        if (!empty($timerTask->actions["method"])) {
            $actions[] = $this->getMethodAction($timerTask);
        }
        return $actions;
    }

    protected function getWorkflowAction(TimerTask $timerTask)
    {
        $action = null;
        if (!empty($timerTask->actions["state"])) {
            $state = $timerTask->actions["state"];
            $currentState = $this->target->getState();
            $workflow = SEManager::getDocument($this->target->wid);
            /** @var Wdoc $workflow */
            if ($workflow) {
                SEManager::cache()->addDocument($workflow);

                $stepInfo = [
                    "nextStateid" => $state,
                    "currentStateLabel" => $this->target->getStepLabel(),
                    "nextStateLabel" => $workflow->getStateLabel($state),
                    "currentStateColor" => $workflow->getColor($currentState),
                    "nextStateColor" => $workflow->getColor($state)
                ];
                foreach ($workflow->cycle as $transition) {
                    if ($transition["e1"] === $currentState && $transition["e2"] === $state) {
                        $stepInfo["transitionId"] = $transition["t"];
                        $stepInfo["transitionLabel"] = $workflow->getTransitionLabel($transition["t"]);
                    }
                }
                if (!empty($stepInfo["transitionId"])) {
                    if ($stepInfo["transitionLabel"] === $stepInfo["transitionId"]) {
                        if ($timerTask->donedate) {
                            $action = [
                                "date" => $timerTask->donedate,
                                "message" => sprintf(
                                    ___("Change state to \"%s\"", "timer"),
                                    $this->target->getTitle(),
                                    $stepInfo["nextStateLabel"]
                                )
                            ];
                        } else {
                            $action = [
                                "date" => $timerTask->tododate,
                                "message" => $this->abortMessage . sprintf(
                                    ___("\"%s\" will change state to \"%s\"", "timer"),
                                    $this->target->getTitle(),
                                    $stepInfo["nextStateLabel"]
                                )
                            ];
                        }
                    } else {
                        if ($timerTask->donedate) {
                            $action = [
                                "date" => $timerTask->donedate,
                                "message" => sprintf(
                                    ___("Run transition \"%s\"", "timer"),
                                    $stepInfo["transitionLabel"],
                                    $stepInfo["currentStateLabel"]
                                )
                            ];
                        } else {
                            $action = [
                                "date" => $timerTask->tododate,
                                "message" => $this->abortMessage . sprintf(
                                    ___("Transition \"%s\" will be executed", "timer"),
                                    $stepInfo["transitionLabel"],
                                    $stepInfo["currentStateLabel"]
                                )
                            ];
                        }
                    }
                } else {
                    $action = [
                        "date" => $timerTask->tododate,
                        "error" => sprintf(
                            ___(
                                "Transition will not succeed because \"%s\" no transition to go to state \"%s\" from \"%s\"",
                                "timer"
                            ),
                            $this->timer->getTitle(),
                            $stepInfo["nextStateLabel"],
                            $stepInfo["currentStateLabel"]
                        )
                    ];
                }
            } else {
                $action = [
                    "date" => $timerTask->tododate,
                    "error" => sprintf(___(
                        "Transition will not succeed because \"%s\" is not linked to a workflow",
                        "timer"
                    ), $this->timer->getTitle())
                ];
            }
        }
        return $action;
    }

    protected function getMailActions(TimerTask $timerTask)
    {
        $actions = [];
        foreach ($timerTask->actions["tmail"] as $mailId) {
            $mailTemplate = SEManager::getDocument($mailId);
            if ($mailTemplate) {
                if ($timerTask->donedate) {
                    $actions[] = [
                        "date" => $timerTask->donedate,
                        "message" => sprintf(___(
                            "Send mail \"%s\"",
                            "timer"
                        ), $mailTemplate->getTitle())
                    ];
                } else {
                    $actions[] = [
                        "date" => $timerTask->tododate,
                        "message" => $this->abortMessage . sprintf(___(
                            "Mail \"%s\" will be send",
                            "timer"
                        ), $mailTemplate->getTitle())
                    ];
                }
            }
        }
        return $actions;
    }

    protected function getMethodAction(TimerTask $timerTask)
    {
        $action = [];
        if ($timerTask->actions["method"]) {
            if ($timerTask->donedate) {
                $action = [
                    "date" => $timerTask->donedate,
                    "message" => sprintf(___(
                        "Method \"%s\" has been executed",
                        "timer"
                    ), $timerTask->actions["method"])
                ];
            } else {
                $action = [
                    "date" => $timerTask->tododate,
                    "message" => $this->abortMessage . sprintf(___(
                        "Method \"%s\" will be executed",
                        "timer"
                    ), $timerTask->actions["method"])
                ];
            }
        }
        return $action;
    }
}
