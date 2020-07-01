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
 * Return prevision for specific timer
 * @note used by route GET /api/v2/admin/sheduling/timers/{timerid}
 */
class ScheduledTimerInfo
{
    protected $timerid = 0;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->timerid=intval($args["timerid"]);
    }


    public function doRequest()
    {
        $data = [];

        $timer =new \DocTimer("", $this->timerid);
          $data = new TimerTask($timer->getValues());


        return $data;
    }


}
