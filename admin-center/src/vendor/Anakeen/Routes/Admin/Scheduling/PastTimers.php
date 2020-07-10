<?php

namespace Anakeen\Routes\Admin\Scheduling;

use Anakeen\Core\TimerManager;
use Anakeen\Core\TimerTask;

/**
 * Return executed timers
 * @note used by route GET /api/v2/admin/sheduling/past-timers/
 */
class PastTimers extends ScheduledTimers
{
    public function doRequest()
    {
        $data = [];

        $timers = TimerManager::getPastTasks();

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
        $data=parent::getTimerData($timerTask);
        $data["doneDate"]=$timerTask->donedate;
        $data["expectedDate"]=$timerTask->tododate;
        $data["result"]=$timerTask->result;
        unset($data["todoDate"]);
        return $data;
    }
}
