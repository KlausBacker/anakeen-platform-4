<?php

namespace Anakeen\Routes\Admin\Stats;

use Anakeen\Core\Internal\StatLogConnectionManager;
use Anakeen\Router\ApiV2Response;

/*
 * @note used by GET /api/v2/stats/connexions/login/months/{from}/{to}
 *
 */

class MonthUserConnexionLogin
{
    /**  @var string */
    protected $fromDate="";
    /** @var string */
    protected $toDate="";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        if (!empty($args["from"])) {
             $this->fromDate = $args["from"]."-01";
        }
        if (!empty($args["to"])) {
            $this->toDate = $args["to"]."-01";
        }
    }

    protected function doRequest()
    {
        return [
            "logins" => StatLogConnectionManager::getMonthsLogin($this->fromDate, $this->toDate),
            "period" => ["from" => $this->fromDate, "to" => $this->toDate],
        ];
    }
}
