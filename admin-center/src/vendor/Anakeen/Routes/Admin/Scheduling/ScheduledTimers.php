<?php

namespace Anakeen\Routes\Admin\Scheduling;

use Anakeen\Core\SEManager;
use Anakeen\Core\TimerManager;
use Anakeen\Core\TimerTask;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchElementData;
use Anakeen\Search\SearchElements;
use Anakeen\Ui\DataSource;
use SmartStructure\Task;
use SmartStructure\Fields\Task as TaskFields;
use SmartStructure\Wdoc;

/**
 * Return prevision for timers
 * @note used by route GET /api/v2/admin/sheduling/timers/
 */
class ScheduledTimers
{
    protected $start = 0;
    protected $slice = -1;
    protected $filters = [];
    protected $fields = [TaskFields::task_status, TaskFields::task_nextdate, TaskFields::task_exec_state_result];
    protected $smartInfo = [];

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

        $timers = TimerManager::getNextTaskToExecute();

        $this->getTitles($timers);
        /*
         * (
            [id] => 94
            [actions] => Array
                (
                    [state] => wfam_bill_e1
                    [tmail] => Array
                        (
                        )

                    [method] =>
                )

            [timerid] => 221200
            [originid] => 111922
            [docid] => 112819
            [title] => Bill 0305 ZIEGLER Alice
            [fromid] => 111918
            [attachdate] => 2020-06-26 11:19:15
            [donedate] =>
            [referencedate] => 2020-06-26 11:19:15
            [tododate] => 2020-06-26 12:19:15
            [result] =>
            [level] => 0
        )
         */
        foreach ($timers as $timer) {
            $data[] = $this->getTimerData($timer);
        }
        // @TODO total , slice take

        return $data;
    }

    protected function getTimerData(TimerTask $timerTask)
    {
        $data = [
            "id" => $timerTask->id,
            "status" => $timerTask->donestatus,
            "attachDate" => $timerTask->attachdate,
            "referenceDate" => $timerTask->referencedate,
            "todoDate" => $timerTask->tododate,
            "attachTo" => $this->smartInfo[$timerTask->docid],
            "timer" => $this->smartInfo[$timerTask->timerid],
            "attachBy" => $this->smartInfo[$timerTask->originid] ?? null
        ];
        $data["mails"] = [];
        foreach ($timerTask->actions["tmail"] as $mail) {
            if ($mail) {
                $data["mails"][] = $this->smartInfo[$mail];
            }
        }

        $data["workflow"] = $this->getStepInfo($timerTask);
        $data["method"] = $timerTask->actions["methods"] ?? '';

        return $data;
    }


    protected function getStepInfo(TimerTask $timer)
    {
        $state = $timer->actions["state"] ?? null;
        if ($state) {
            $wid = $this->smartInfo[$timer->docid]["wid"] ?? "";

            if ($wid) {
                $currentState=$this->smartInfo[$timer->docid]["state"];
                $workflow = SEManager::getDocument($wid);
                /** @var Wdoc $workflow */
                if ($workflow) {
                    SEManager::cache()->addDocument($workflow);

                    $stepInfo= [
                        "nextStateid" => $state,
                        "nextStateLabel" => $workflow->getStateLabel($state),
                        "currentStateColor" => $workflow->getColor($currentState),
                        "nextStateColor" => $workflow->getColor($state)
                    ];
                    foreach ($workflow->cycle as $transition) {
                        if ($transition["e1"] ===$currentState && $transition["e2"] ===$state) {
                            $stepInfo["transitionId"] =  $transition["t"];
                            $stepInfo["transitionLabel"] =  $workflow->getTransitionLabel($transition["t"]);
                        }
                    }
                    return $stepInfo;
                }
            }
        }
        return null;
    }


    /**
     * @param TimerTask[] $timers
     */
    protected function getTitles($timers)
    {
        $seIds = [];
        foreach ($timers as $timer) {
            if ($timer->docid) {
                $seIds[$timer->docid] = true;
            }
            if ($timer->originid) {
                $seIds[$timer->originid] = true;
            }
            if ($timer->timerid) {
                $seIds[$timer->timerid] = true;
            }
            foreach ($timer->actions["tmail"] as $mail) {
                if ($mail) {
                    $seIds[$mail] = true;
                }
            }
        }
        $s = new SearchElementData();
        //$s->setLatest(false);
        $s->returnsOnly(["title", "initid", "id", "name", "wid", "state"]);
        $s->addFilter(\Anakeen\Search\Internal\SearchSmartData::sqlcond(array_keys($seIds), "initid", true));

        $s->search();

        $results = $s->getResults();
        foreach ($results as $result) {
            $this->smartInfo[$result["initid"]] = $result;
        }
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
            "id" => $task->id,
            "initid" => $task->initid,
            "name" => $task->name,
            "title" => $task->getTitle()
        ];

        foreach ($this->fields as $field) {
            $data[$field] = $task->getRawValue($field);
        }
        return $data;
    }
}
